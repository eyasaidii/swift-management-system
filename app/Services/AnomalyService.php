<?php

namespace App\Services;

use App\Models\AnomalySwift;
use App\Models\MessageSwift;

class AnomalyService
{
    public function analyze(MessageSwift $message): array
    {
        $score = 0;
        $raisons = [];

        // ── Règle 1 — Montant zéro ──────────────────────────────
        $amount = (float) ($message->AMOUNT ?? $message->amount ?? 0);
        if ($amount == 0) {
            $score += 40;
            $raisons[] = 'MONTANT_ZERO';
        }

        // ── Règle 2 — Montant très élevé (> 100 000) ────────────
        if ($amount > 100000) {
            $score += 25;
            $raisons[] = 'MONTANT_ELEVE';
        }

        // ── Règle 3 — Statut rejeté / rejected ──────────────────
        $status = strtolower(trim($message->STATUS ?? $message->status ?? ''));
        if (in_array($status, ['rejected', 'rejeté', 'rejete', 'rejet'])) {
            $score += 30;
            $raisons[] = 'STATUT_REJETE';
        }

        // ── Règle 4 — Erreur de traduction XML ──────────────────
        $translationErrors = $message->TRANSLATION_ERRORS
                          ?? $message->translation_errors
                          ?? null;
        if (! empty($translationErrors)) {
            $score += 25;
            $raisons[] = 'TRANSLATION_ERROR';
        }

        // ── Règle 5 — Type ERROR ─────────────────────────────────
        $typeMessage = strtoupper(trim($message->TYPE_MESSAGE ?? $message->type_message ?? ''));
        if ($typeMessage === 'ERROR' || str_contains($typeMessage, 'ERROR')) {
            $score += 35;
            $raisons[] = 'TYPE_ERROR';
        }

        // ── Règle 6 — Doublon de référence ───────────────────────
        $reference = $message->REFERENCE ?? $message->reference ?? null;
        if ($reference) {
            $doublon = MessageSwift::where('REFERENCE', $reference)
                ->where('id', '!=', $message->id)
                ->exists();
            if ($doublon) {
                $score += 20;
                $raisons[] = 'DOUBLON_REFERENCE';
            }
        }

        // ── Règle 7 — BIC manquant ───────────────────────────────
        $senderBic = $message->SENDER_BIC ?? $message->sender_bic ?? null;
        $receiverBic = $message->RECEIVER_BIC ?? $message->receiver_bic ?? null;
        if (empty($senderBic) || empty($receiverBic)) {
            $score += 15;
            $raisons[] = 'BIC_MANQUANT';
        }

        // ── Règle 8 — Devise inhabituelle ────────────────────────
        $currency = strtoupper(trim($message->CURRENCY ?? $message->currency ?? ''));
        $devicesHabituelles = ['EUR', 'USD', 'TND', 'GBP', 'CHF'];
        if (! empty($currency) && ! in_array($currency, $devicesHabituelles)) {
            $score += 20;
            $raisons[] = 'DEVISE_INHABITUELLE';
        }

        // ── Règle 9 — Référence IMPORT-FAILED ───────────────────
        if ($reference && str_contains(strtoupper($reference), 'IMPORT-FAILED')) {
            $score += 40;
            $raisons[] = 'IMPORT_FAILED';
        }

        // ── Règle 10 — Bénéficiaire contient numéro passeport ───
        $receiverName = strtoupper($message->RECEIVER_NAME ?? $message->receiver_name ?? '');
        if (preg_match('/PASS(PORT)?\s*(NO|NUM|N°)?\s*[A-Z]{0,2}\d{5,}/i', $receiverName)) {
            $score += 30;
            $raisons[] = 'PASSPORT_DETECTE';
        }

        // ── Score final ───────────────────────────────────────────
        $score = min($score, 100);
        $niveau = match (true) {
            $score >= 60 => 'HIGH',
            $score >= 20 => 'MEDIUM',
            default => 'LOW',
        };

        // Sauvegarder dans ANOMALIES_SWIFT
        AnomalySwift::updateOrCreate(
            ['message_id' => $message->id],
            [
                'score' => $score,
                'niveau_risque' => $niveau,
                'raisons' => $raisons,
            ]
        );

        return [
            'score' => $score,
            'niveau_risque' => $niveau,
            'raisons' => $raisons,
        ];
    }

    public function analyzeAll(): void
    {
        MessageSwift::chunk(100, function ($messages) {
            foreach ($messages as $message) {
                try {
                    $this->analyze($message);
                } catch (\Throwable $e) {
                    \Log::warning("Analyse échouée #{$message->id} : {$e->getMessage()}");
                }
            }
        });
    }
}
