<?php

// app/Console/Commands/SyncTransactionsFromMessages.php

namespace App\Console\Commands;

use App\Helpers\SwiftParser;
use App\Models\MessageSwift;
use App\Models\Transaction;
use Illuminate\Console\Command;

class SyncTransactionsFromMessages extends Command
{
    protected $signature = 'swift:sync-transactions
                                {--force : Recréer les transactions même si déjà existantes}
                                {--direction= : Filtrer par direction IN ou OUT}
                                {--dry-run : Afficher sans créer}';

    protected $description = 'Crée/met à jour les transactions à partir des messages SWIFT';

    /** Types de messages financiers */
    private const FINANCIAL_TYPES = [
        'PACS.008', 'PACS.009',
        'MT103', 'MT202', 'MT940',
        'CAMT.053', 'CAMT.054',
    ];

    public function handle(): int
    {
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');
        $direction = strtoupper($this->option('direction') ?? '');

        $this->info('══════════════════════════════════════');
        $this->info(' BTL SWIFT — Sync Transactions');
        $this->info('══════════════════════════════════════');

        // ── Requête de base ──
        $query = MessageSwift::whereIn('TYPE_MESSAGE', self::FINANCIAL_TYPES);

        if ($direction === 'IN' || $direction === 'OUT') {
            $query->where('DIRECTION', $direction);
            $this->line("  Direction filtrée : {$direction}");
        }

        $messages = $query->get();
        $this->line("  Messages trouvés : {$messages->count()}");
        $this->line('');

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $fixed = 0;
        $errors = 0;

        foreach ($messages as $message) {

            // ── Récupérer ou reconstruire le montant ──
            $amount = (float) ($message->AMOUNT ?? $message->amount ?? 0);
            $currency = $message->CURRENCY ?? $message->currency ?? null;
            $valDate = $message->VALUE_DATE ?? $message->value_date ?? null;

            // Fallback : lire depuis le tag 32A si amount = 0
            if ($amount <= 0 || ! $currency) {
                $tag32A = $message->details()->where('tag_name', '32A')->value('tag_value');
                if ($tag32A) {
                    [$parsedDate, $parsedCcy, $parsedAmt] = SwiftParser::parse32A($tag32A);
                    if ($parsedAmt > 0) {
                        $amount = $parsedAmt;
                        $currency = $parsedCcy ?? $currency;
                        $valDate = $parsedDate ?? $valDate;

                        // Mettre à jour le message avec les bonnes valeurs
                        if (! $dryRun) {
                            $updatePayload = [];
                            if ($parsedAmt && ! ($message->AMOUNT ?? $message->amount)) {
                                $updatePayload['AMOUNT'] = $parsedAmt;
                            }
                            if ($parsedCcy && ! ($message->CURRENCY ?? $message->currency)) {
                                $updatePayload['CURRENCY'] = $parsedCcy;
                            }
                            if ($parsedDate && ! ($message->VALUE_DATE ?? $message->value_date)) {
                                $updatePayload['VALUE_DATE'] = $parsedDate;
                            }
                            if (! empty($updatePayload)) {
                                MessageSwift::withoutEvents(fn () => $message->update($updatePayload));
                                $fixed++;
                            }
                        }
                    }
                }
            }

            // ── Ignorer si toujours pas de données financières ──
            if ($amount <= 0 || ! $currency) {
                $this->warn("  ⚠ SKIP #{$message->id} ({$message->TYPE_MESSAGE}) — montant ou devise manquant");
                $skipped++;

                continue;
            }

            // ── Vérifier si transaction existe déjà ──
            $existing = Transaction::where('message_swift_id', $message->id)->first();

            if ($existing && ! $force) {
                $skipped++;

                continue;
            }

            // ── Déterminer émetteur / récepteur ──
            $dir = strtoupper($message->DIRECTION ?? $message->direction ?? 'IN');

            if ($dir === 'IN') {
                $emetteur = $message->SENDER_NAME ?? $message->sender_name
                          ?? $message->SENDER_BIC ?? $message->sender_bic
                          ?? 'Banque externe';
                $recepteur = 'BTL Bank';
            } else {
                $emetteur = 'BTL Bank';
                $recepteur = $message->RECEIVER_NAME ?? $message->receiver_name
                          ?? $message->RECEIVER_BIC ?? $message->receiver_bic
                          ?? 'Bénéficiaire externe';
            }

            $dateTransaction = $valDate ?? $message->CREATED_AT ?? $message->created_at ?? now();

            if ($dryRun) {
                $action = $existing ? 'UPDATE' : 'CREATE';
                $this->line("  [{$action}] #{$message->id} {$message->TYPE_MESSAGE} {$dir} — {$amount} {$currency} — {$emetteur} → {$recepteur}");
                $existing ? $updated++ : $created++;

                continue;
            }

            try {
                $wasExisting = (bool) $existing;

                Transaction::updateOrCreate(
                    ['message_swift_id' => $message->id],
                    [
                        'montant' => $amount,
                        'devise' => $currency,
                        'emetteur' => $emetteur,
                        'recepteur' => $recepteur,
                        'date_transaction' => $dateTransaction,
                    ]
                );

                if ($wasExisting) {
                    $updated++;
                } else {
                    $created++;
                    $this->line("  ✅ Créé  #{$message->id} {$message->TYPE_MESSAGE} [{$dir}] — {$amount} {$currency}");
                }

            } catch (\Throwable $e) {
                $errors++;
                $this->error("  ❌ Erreur #{$message->id} : {$e->getMessage()}");
            }
        }

        // ── Résumé ──
        $this->line('');
        $this->info('══════════════════════════════════════');
        $this->info(" ✅ Créées   : {$created}");
        $this->info(" 🔄 Mises à jour : {$updated}");
        if ($fixed > 0) {
            $this->info(" 🔧 Montants corrigés : {$fixed}");
        }
        if ($skipped > 0) {
            $this->line(" ⏭ Ignorées : {$skipped}");
        }
        if ($errors > 0) {
            $this->error(" ❌ Erreurs  : {$errors}");
        }
        $this->info('══════════════════════════════════════');

        if ($dryRun) {
            $this->warn('  Mode dry-run : aucune modification effectuée.');
        }

        return Command::SUCCESS;
    }
}
