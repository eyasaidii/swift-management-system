<?php

// app/Observers/MessageSwiftObserver.php

namespace App\Observers;

use App\Models\MessageSwift;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

/**
 * MessageSwiftObserver
 *
 * FLUX MESSAGES REÇUS (IN) — ProcessSwiftFileJob :
 *   1. MessageSwift::create([...tous les champs financiers inclus...])
 *      → created() se déclenche ICI avec amount/currency/value_date ✅
 *
 * FLUX MESSAGES ÉMIS (OUT) — MessageSwiftController::store() :
 *   1. MessageSwift::create([type, direction, status])
 *      → created() se déclenche ICI mais amount=null → IGNORÉ ✅
 *   2. $message->details()->create(...)
 *   3. $message->update([AMOUNT, CURRENCY, VALUE_DATE, CATEGORIE])
 *      → updated() se déclenche ICI avec toutes les données ✅
 *   4. MessageSwift::withoutEvents(fn => update XML_BRUT)
 *      → PAS de déclenchement ✅
 */
class MessageSwiftObserver
{
    /** Types de messages financiers qui génèrent une transaction */
    private const FINANCIAL_TYPES = [
        'PACS.008', 'PACS.009',
        'MT103', 'MT202', 'MT940',
        'CAMT.053', 'CAMT.054',
    ];

    // ─────────────────────────────────────────────────────────
    // created() — déclenché après MessageSwift::create()
    // Pour les messages IN : le create() inclut déjà amount/currency
    // Pour les messages OUT : amount=null → ignoré
    // ─────────────────────────────────────────────────────────
    public function created(MessageSwift $message): void
    {
        $this->syncTransaction($message, 'created');
    }

    // ─────────────────────────────────────────────────────────
    // updated() — déclenché après $message->update() ou save()
    // Pour les messages OUT : c'est ici que amount est disponible
    // Pour les messages IN : mis à jour par applyCommonMapping()
    // ─────────────────────────────────────────────────────────
    public function updated(MessageSwift $message): void
    {
        // Déclencher seulement si un champ financier a changé
        $dirty = array_keys($message->getDirty());
        $financialFields = ['AMOUNT', 'CURRENCY', 'VALUE_DATE', 'amount', 'currency', 'value_date'];

        if (! empty(array_intersect($financialFields, $dirty))) {
            $this->syncTransaction($message, 'updated');
        }
    }

    // ─────────────────────────────────────────────────────────
    // SYNC — créer ou mettre à jour la transaction associée
    // ─────────────────────────────────────────────────────────
    private function syncTransaction(MessageSwift $message, string $trigger): void
    {
        // Guard 1 : type financier
        if (! in_array($message->TYPE_MESSAGE, self::FINANCIAL_TYPES)) {
            return;
        }

        // Guard 2 : données financières disponibles et valides
        $amount = $message->AMOUNT ?? $message->amount ?? null;
        $currency = $message->CURRENCY ?? $message->currency ?? null;

        if ($amount === null || $currency === null || (float) $amount <= 0) {
            return;
        }

        // Guard 3 : éviter les recréations inutiles
        $existing = Transaction::where('message_swift_id', $message->id)->first();
        if ($existing) {
            $needsUpdate =
                abs((float) $existing->montant - (float) $amount) > 0.001 ||
                $existing->devise !== $currency;

            if (! $needsUpdate) {
                return;
            }
        }

        // Déterminer direction
        $direction = strtoupper($message->DIRECTION ?? $message->direction ?? 'IN');

        // Émetteur / Récepteur
        if ($direction === 'IN') {
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

        // Date transaction
        $dateTransaction = $message->VALUE_DATE ?? $message->value_date
                        ?? $message->CREATED_AT ?? $message->created_at
                        ?? now();

        try {
            Transaction::updateOrCreate(
                ['message_swift_id' => $message->id],
                [
                    'montant' => (float) $amount,
                    'devise' => $currency,
                    'emetteur' => $emetteur,
                    'recepteur' => $recepteur,
                    'date_transaction' => $dateTransaction,
                ]
            );

            Log::info("Transaction synced [{$trigger}]", [
                'message_id' => $message->id,
                'type' => $message->TYPE_MESSAGE,
                'direction' => $direction,
                'amount' => $amount,
                'currency' => $currency,
            ]);

        } catch (\Throwable $e) {
            Log::error('Transaction sync failed', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
