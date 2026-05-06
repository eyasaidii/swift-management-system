<?php

namespace App\Services;

use App\Models\AnomalySwift;
use App\Models\MessageSwift;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnomalyService
{
    // Types qui impliquent un virement (MONTANT_ZERO ne s'applique QUE sur ces types)
    private const PAYMENT_TYPES = ['MT103', 'MT202', 'PACS.008', 'PACS.009', 'PAIN.001'];

    // Devises normales BTL Bank
    private const DEVISES_NORMALES = ['EUR', 'USD', 'TND', 'GBP', 'CHF'];

    // URL microservice Python FastAPI
    // docker-compose : http://python-api:8001  |  local : http://localhost:8001
    private string $pythonApiUrl;
    private int    $apiTimeout;

public function __construct()
    {
        $this->pythonApiUrl = env('AI_SERVICE_URL', 'http://python-api:8001');
        $this->apiTimeout   = (int) env('AI_SERVICE_TIMEOUT', 5);
    }

    public function analyze(MessageSwift $message): array
    {
        $score   = 0;
        $raisons = [];

        // Champs normalises (MAJUSCULES Oracle + fallback minuscules)
        $amount            = (float) ($message->AMOUNT            ?? $message->amount            ?? 0);
        $status            = strtolower(trim($message->STATUS     ?? $message->status            ?? ''));
        $typeMessage       = strtoupper(trim($message->TYPE_MESSAGE ?? $message->type_message    ?? ''));
        $reference         = $message->REFERENCE                  ?? $message->reference         ?? null;
        $currency          = strtoupper(trim($message->CURRENCY   ?? $message->currency          ?? ''));
        $senderBic         = $message->SENDER_BIC                 ?? $message->sender_bic        ?? null;
        $receiverBic       = $message->RECEIVER_BIC               ?? $message->receiver_bic      ?? null;
        $receiverName      = strtoupper($message->RECEIVER_NAME   ?? $message->receiver_name     ?? '');
        $translationErrors = $message->TRANSLATION_ERRORS         ?? $message->translation_errors ?? null;

        // Regle 1 - Montant zero sur un message de virement (+40)
        // CORRECTION : seulement pour PAYMENT_TYPES (MT940/CAMT.053 ont amount=0 normalement)
        if ($amount == 0 && in_array($typeMessage, self::PAYMENT_TYPES)) {
            $score += 40;
            $raisons[] = 'MONTANT_ZERO';
        }

        // Regle 2 - Montant tres eleve > 100 000 (+25)
        if ($amount > 100000) {
            $score += 25;
            $raisons[] = 'MONTANT_ELEVE';
        }

        // Regle 3 - Statut rejete (+30)
        if (in_array($status, ['rejected', 'rejeté', 'rejete', 'rejet'])) {
            $score += 30;
            $raisons[] = 'STATUT_REJETE';
        }

        // Regle 4 - Erreur de traduction XML->MT (+25)
        if (! empty($translationErrors) && $translationErrors !== '[]' && $translationErrors !== 'null') {
            $score += 25;
            $raisons[] = 'TRANSLATION_ERROR';
        }

        // Regle 5 - Reference contient IMPORT-FAILED ou ERROR (+35)
        // CORRECTION : verifie REFERENCE (pas TYPE_MESSAGE qui ne contient jamais "ERROR")
        $refUpper = strtoupper((string) $reference);
        if ($reference && (str_contains($refUpper, 'IMPORT-FAILED') || str_contains($refUpper, 'ERROR'))) {
            $score += 35;
            $raisons[] = 'TYPE_ERROR';
        }

        // Regle 6 - Doublon de reference (+20)
        if ($reference) {
            $doublon = MessageSwift::where('REFERENCE', $reference)
                ->where('id', '!=', $message->id)
                ->exists();
            if ($doublon) {
                $score += 20;
                $raisons[] = 'DOUBLON_REFERENCE';
            }
        }

        // Regle 7 - BIC manquant (+15)
        if (empty($senderBic) || empty($receiverBic)) {
            $score += 15;
            $raisons[] = 'BIC_MANQUANT';
        }

        // Regle 8 - Devise inhabituelle (+20)
        if (! empty($currency) && ! in_array($currency, self::DEVISES_NORMALES)) {
            $score += 20;
            $raisons[] = 'DEVISE_INHABITUELLE';
        }

        // Regle 9 - Numero passeport dans le nom beneficiaire (+30)
        if (preg_match('/PASS\s*(PORT)?\s*(NO|NUM|N°)?\s*[A-Z]{0,2}\d{5,}/i', $receiverName)) {
            $score += 30;
            $raisons[] = 'PASSPORT_DETECTE';
        }

        // Score regles plafonne a 100
        $scoreRegles = min($score, 100);

        // Score ML via microservice Python FastAPI
        $scoreMl = $this->callPythonApi($message);

        // Formule hybride : (regles x 60%) + (ML x 40%), max 100
        $scoreFinal = min(($scoreRegles * 0.60) + ($scoreMl * 0.40), 100);
        $scoreFinal = round($scoreFinal, 2);

        // Mention ML dans les raisons si score eleve
        if ($scoreMl >= 50) {
            $raisons[] = 'ML_SCORE_' . round($scoreMl);
        }

        // Niveau de risque selon seuils BTL
        $niveau = match (true) {
            $scoreFinal >= 60 => 'HIGH',
            $scoreFinal >= 20 => 'MEDIUM',
            default           => 'LOW',
        };

        // Sauvegarder dans ANOMALIES_SWIFT
        AnomalySwift::updateOrCreate(
            ['message_id' => $message->id],
            [
                'score'         => $scoreFinal,
                'niveau_risque' => $niveau,
                'raisons'       => $raisons,
            ]
        );

        if ($niveau === 'HIGH') {
            Log::warning("SWIFT HIGH RISK — Message #{$message->id} | Score: {$scoreFinal} | Ref: {$reference}");
        }

        return [
            'score'         => $scoreFinal,
            'score_regles'  => $scoreRegles,
            'score_ml'      => $scoreMl,
            'niveau_risque' => $niveau,
            'raisons'       => $raisons,
        ];
    }

    // Appel HTTP vers Python FastAPI
    // Retourne score_ml (0-100), 0 si le service est indisponible (fallback 100% regles)
    private function callPythonApi(MessageSwift $message): float
    {
        try {
            $response = Http::timeout($this->apiTimeout)
                ->post("{$this->pythonApiUrl}/api/predict", [
                    'id'                 => $message->id,
                    'type_message'       => $message->TYPE_MESSAGE       ?? $message->type_message,
                    'direction'          => $message->DIRECTION           ?? $message->direction,
                    'sender_bic'         => $message->SENDER_BIC         ?? $message->sender_bic,
                    'receiver_bic'       => $message->RECEIVER_BIC       ?? $message->receiver_bic,
                    'sender_name'        => $message->SENDER_NAME        ?? $message->sender_name,
                    'receiver_name'      => $message->RECEIVER_NAME      ?? $message->receiver_name,
                    'amount'             => (float) ($message->AMOUNT    ?? $message->amount ?? 0),
                    'currency'           => $message->CURRENCY            ?? $message->currency,
                    'value_date'         => optional($message->VALUE_DATE)->format('Y-m-d'),
                    'created_at'         => optional($message->CREATED_AT)->format('Y-m-d\TH:i:s'),
                    'reference'          => $message->REFERENCE           ?? $message->reference,
                    'status'             => $message->STATUS              ?? $message->status,
                    'translation_errors' => $message->TRANSLATION_ERRORS ?? $message->translation_errors,
                    'category'           => $message->CATEGORY            ?? $message->category,
                ]);

            if ($response->successful()) {
                return (float) ($response->json('score_ml') ?? 0);
            }

            Log::warning("AnomalyService: Python API HTTP {$response->status()} — message #{$message->id}");
            return 0.0;

        } catch (\Illuminate\Http\Client\ConnectionException) {
            // Service IA indisponible -> fallback 100% regles, pas d'erreur visible
            return 0.0;
        } catch (\Exception $e) {
            Log::error("AnomalyService: Erreur Python API — {$e->getMessage()}");
            return 0.0;
        }
    }

    // Re-analyser tous les messages en batch
    public function analyzeAll(): void
    {
        MessageSwift::chunk(100, function ($messages) {
            foreach ($messages as $message) {
                try {
                    $this->analyze($message);
                } catch (\Throwable $e) {
                    Log::warning("Analyse echouee #{$message->id} : {$e->getMessage()}");
                }
            }
        });
    }
}