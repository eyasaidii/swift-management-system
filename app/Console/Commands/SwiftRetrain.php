<?php

namespace App\Console\Commands;

use App\Jobs\RetrainModelJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * swift:retrain — Ré-entraîne le modèle IA sur les données Oracle actuelles.
 *
 * Usage :
 *   php artisan swift:retrain           # déclenchement en arrière-plan (queue)
 *   php artisan swift:retrain --sync    # déclenchement synchrone (attend la fin)
 *   php artisan swift:retrain --status  # affiche le statut du dernier entraînement
 */
class SwiftRetrain extends Command
{
    protected $signature   = 'swift:retrain {--sync : Attendre la fin du ré-entraînement} {--status : Afficher le statut du dernier entraînement}';
    protected $description = 'Ré-entraîne le modèle IA de détection d\'anomalies sur les données Oracle';

    public function handle(): int
    {
        if ($this->option('status')) {
            return $this->showStatus();
        }

        $aiUrl = rtrim(config('services.anomaly_ai.url', 'http://python-api:8001'), '/');

        // Vérifier que le service Python est disponible
        try {
            $health = Http::timeout(5)->get("{$aiUrl}/api/health");
            if (!$health->successful()) {
                $this->error("Service IA indisponible ({$health->status()})");
                return self::FAILURE;
            }
        } catch (\Throwable $e) {
            $this->error("Impossible de joindre le service IA : {$e->getMessage()}");
            return self::FAILURE;
        }

        if ($this->option('sync')) {
            // Mode synchrone : appel direct et attente
            $this->info("Ré-entraînement synchrone en cours...");
            $this->newLine();

            $bar = $this->output->createProgressBar(3);
            $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
            $bar->setMessage('Export données Oracle...');
            $bar->start();

            try {
                $bar->setMessage('Entraînement XGBoost + IsolationForest...');
                $bar->advance();

                $response = Http::timeout(270)->post("{$aiUrl}/api/train-from-oracle/sync");

                $bar->setMessage('Sauvegarde du modèle...');
                $bar->advance();

                if ($response->successful()) {
                    $data    = $response->json();
                    $version = $data['model_version'] ?? 'unknown';
                    $samples = $data['samples_used']  ?? '?';

                    Cache::put('ai_model_version',        $version, now()->addDays(30));
                    Cache::put('ai_last_retrain_at',      now()->toDateTimeString(), now()->addDays(30));
                    Cache::put('ai_last_retrain_samples', $samples, now()->addDays(30));
                    Cache::put('ai_retrain_running',      false, now()->addHours(1));

                    $bar->setMessage('Terminé !');
                    $bar->finish();
                    $this->newLine(2);

                    $this->info("Ré-entraînement terminé avec succès !");
                    $this->table(
                        ['Paramètre', 'Valeur'],
                        [
                            ['Version modèle', $version],
                            ['Échantillons utilisés', $samples],
                            ['Date', now()->format('Y-m-d H:i:s')],
                        ]
                    );
                    return self::SUCCESS;
                } else {
                    $bar->finish();
                    $this->newLine();
                    $this->error("Erreur Python : {$response->status()} — {$response->body()}");
                    return self::FAILURE;
                }

            } catch (\Throwable $e) {
                $bar->finish();
                $this->newLine();
                $this->error("Erreur : {$e->getMessage()}");
                return self::FAILURE;
            }
        }

        // Mode asynchrone (par défaut) : dispatch dans la queue
        $count = (int) Cache::get('anomaly_analyzed_count', 0);
        Cache::put('ai_retrain_running', true, now()->addMinutes(10));
        RetrainModelJob::dispatch($count);

        $this->info("Ré-entraînement lancé en arrière-plan.");
        $this->line("  Vérifier le statut : php artisan swift:retrain --status");
        return self::SUCCESS;
    }

    private function showStatus(): int
    {
        $version  = Cache::get('ai_model_version',        'inconnu');
        $lastAt   = Cache::get('ai_last_retrain_at',      'jamais');
        $samples  = Cache::get('ai_last_retrain_samples', '?');
        $running  = Cache::get('ai_retrain_running',      false);
        $count    = Cache::get('anomaly_analyzed_count',  0);
        $threshold = (int) config('services.anomaly_ai.retrain_threshold', 50);

        $this->info("=== Statut du modèle IA ===");
        $this->table(
            ['Paramètre', 'Valeur'],
            [
                ['Version actuelle',          $version],
                ['Dernier entraînement',      $lastAt],
                ['Échantillons utilisés',     $samples],
                ['En cours de ré-entraînement', $running ? 'OUI' : 'non'],
                ['Messages analysés (compteur)', $count],
                ['Seuil auto-retrain',        $threshold . ' messages'],
                ['Prochain retrain dans',     max(0, $threshold - ($count % $threshold)) . ' messages'],
            ]
        );
        return self::SUCCESS;
    }
}
