<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RetrainModelCommand extends Command
{
    protected $signature = 'retrain:model {--sync : Attendre la fin du réentraînement}';
    protected $description = 'Déclenche le réentraînement du modèle IA depuis Oracle';

    public function handle(): int
    {
        $aiUrl = rtrim(config('services.anomaly_ai.url', 'http://python-api:8001'), '/');
        $endpoint = $this->option('sync')
            ? "{$aiUrl}/api/train-from-oracle/sync"
            : "{$aiUrl}/api/train-from-oracle";

        $this->info("Réentraînement IA depuis Oracle...");
        $this->info("URL : {$endpoint}");

        try {
            $response = Http::timeout(300)->post($endpoint);

            if ($response->successful()) {
                $data = $response->json();
                $this->info("✅ ".$data['message']);
                if (isset($data['model_version'])) {
                    $this->line("   Version : ".$data['model_version']);
                }
                if (isset($data['samples_used'])) {
                    $this->line("   Échantillons : ".$data['samples_used']);
                }
                return self::SUCCESS;
            }

            $this->error("Erreur IA : ".$response->status()." — ".$response->body());
            return self::FAILURE;

        } catch (\Throwable $e) {
            $this->error("Exception : ".$e->getMessage());
            Log::error('retrain:model — '.$e->getMessage());
            return self::FAILURE;
        }
    }
}
