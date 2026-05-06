<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * RetrainModelJob
 *
 * Déclenché automatiquement par AnomalyService après chaque N messages analysés.
 * Appelle le microservice Python POST /api/train-from-oracle qui :
 *   1. Récupère les données Oracle via GET /api/swift-export (Laravel)
 *   2. Ré-entraîne XGBoost + IsolationForest
 *   3. Sauvegarde le nouveau modèle
 */
class RetrainModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 300; // 5 min max

    public function __construct(
        public readonly int $triggerCount = 0,
    ) {}

    public function handle(): void
    {
        $aiUrl = rtrim(config('services.anomaly_ai.url', 'http://python-api:8001'), '/');

        Log::info("RetrainModelJob: déclenchement ré-entraînement (trigger_count={$this->triggerCount})");

        try {
            // Appel synchrone — attend la fin du ré-entraînement
            $response = Http::timeout(270)->post("{$aiUrl}/api/train-from-oracle/sync");

            if ($response->successful()) {
                $data = $response->json();
                $version = $data['model_version'] ?? 'unknown';
                $samples = $data['samples_used']  ?? '?';

                Log::info("RetrainModelJob: succès — version={$version}, samples={$samples}");

                // Stocker la version du dernier entraînement
                Cache::put('ai_model_version',       $version, now()->addDays(30));
                Cache::put('ai_last_retrain_at',     now()->toDateTimeString(), now()->addDays(30));
                Cache::put('ai_last_retrain_samples', $samples, now()->addDays(30));

                // Marquer que le ré-entraînement est terminé
                Cache::put('ai_retrain_running', false, now()->addHours(1));
            } else {
                Log::warning("RetrainModelJob: réponse {$response->status()} — {$response->body()}");
                Cache::put('ai_retrain_running', false, now()->addHours(1));
            }

        } catch (\Throwable $e) {
            Log::error("RetrainModelJob: erreur — {$e->getMessage()}");
            Cache::put('ai_retrain_running', false, now()->addHours(1));
            throw $e;
        }
    }
}
