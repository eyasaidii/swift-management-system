<?php

namespace App\Console\Commands;

use App\Models\AnomalySwift;
use App\Models\MessageSwift;
use App\Services\AnomalyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * php artisan anomaly:detect                → tous les messages
 * php artisan anomaly:detect --id=42        → un seul message
 * php artisan anomaly:detect --only-new     → seulement ceux sans analyse
 * php artisan anomaly:detect --status=rejected
 * php artisan anomaly:detect --limit=500
 */
class DetectAnomalies extends Command
{
    protected $signature = 'anomaly:detect
                                {--id=       : Analyser un seul message par son ID}
                                {--limit=0   : Nombre max de messages (0 = tous)}
                                {--only-new  : Seulement les messages sans résultat dans ANOMALIES_SWIFT}
                                {--status=   : Filtrer par statut (ex: rejected)}';

    protected $description = 'Détecte les anomalies dans MESSAGES_SWIFT via le service IA (XGBoost + Isolation Forest)';

    public function handle(AnomalyService $service): int
    {
        $aiUrl = rtrim(config('services.anomaly_ai.url', 'http://127.0.0.1:8001'), '/');

        $this->info('═══════════════════════════════════════════════');
        $this->info('  BTL SWIFT — Détection d\'anomalies');
        $this->info('═══════════════════════════════════════════════');

        // Vérification du service IA
        $iaOnline = false;
        try {
            $h = Http::timeout(3)->get("{$aiUrl}/api/health");
            if ($h->successful()) {
                $iaOnline = true;
                $version = $h->json('model_version') ?? '?';
                $this->line("  Service IA   : <info>En ligne</info> — {$version}");
            }
        } catch (\Throwable) {
        }

        if (! $iaOnline) {
            $this->error('  Service IA   : HORS LIGNE — démarrez swift-IA avant de relancer');

            return self::FAILURE;
        }

        // ── Cas : message unique
        if ($id = $this->option('id')) {
            $message = MessageSwift::find((int) $id);
            if (! $message) {
                $this->error("  Message #{$id} introuvable.");

                return self::FAILURE;
            }
            $result = $service->analyze($message);
            $this->line('');
            $this->line("  Message #$id — {$message->TYPE_MESSAGE} — {$message->AMOUNT} {$message->CURRENCY}");
            $this->line("  Score        : {$result['score']}");
            $this->line("  Niveau       : {$result['niveau_risque']}");
            $this->line("  Source       : {$result['source']}");
            $this->line('  Raisons      : '.implode(', ', $result['raisons'] ?: ['Aucune']));

            return self::SUCCESS;
        }

        // ── Cas : traitement en masse
        $query = MessageSwift::query();

        if ($this->option('only-new')) {
            $alreadyDone = AnomalySwift::pluck('message_id');
            $query->whereNotIn('id', $alreadyDone);
            $this->line('  Mode         : nouveaux messages uniquement');
        }

        if ($status = $this->option('status')) {
            $query->where('STATUS', $status);
            $this->line("  Filtre statut: {$status}");
        }

        $total = $query->count();
        $limit = (int) $this->option('limit');
        $toProcess = $limit > 0 ? min($limit, $total) : $total;

        $this->line("  Messages     : {$toProcess}");
        $this->line('');

        if ($toProcess === 0) {
            $this->info('  Rien à traiter.');

            return self::SUCCESS;
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $bar = $this->output->createProgressBar($toProcess);
        $bar->start();

        $ok = 0;
        $errors = 0;
        $anomalies = 0;
        $iaCount = 0;
        $rulesCount = 0;

        $query->chunk(50, function ($messages) use ($service, $bar, &$ok, &$errors, &$anomalies, &$iaCount, &$rulesCount) {
            foreach ($messages as $message) {
                try {
                    $result = $service->analyze($message);
                    $ok++;
                    if ($result['niveau_risque'] !== 'LOW') {
                        $anomalies++;
                    }
                    if ($result['source'] === 'IA') {
                        $iaCount++;
                    } else {
                        $rulesCount++;
                    }
                } catch (\Throwable $e) {
                    $errors++;
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->line("\n");
        $this->info('  Résultats :');
        $this->line("  ✓ Analysés   : {$ok}");
        $this->line("  ⚠ Suspects   : {$anomalies} (MEDIUM ou HIGH)");
        $this->line("  🤖 Via IA     : {$iaCount}");
        if ($errors > 0) {
            $this->warn("  ✗ Erreurs    : {$errors}");
        }

        return self::SUCCESS;
    }
}
