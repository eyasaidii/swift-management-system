<?php

namespace App\Services;

use App\Models\AnomalySwift;
use App\Models\MessageSwift;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnomalyService
{
    private string $aiUrl;

    private int $aiTimeout;

    public function __construct()
    {
        $this->aiUrl = rtrim(config('services.anomaly_ai.url', 'http://127.0.0.1:8001'), '/');
        $this->aiTimeout = (int) config('services.anomaly_ai.timeout', 10);
    }

    // ─────────────────────────────────────────────────────────────
    // Analyse principale : uniquement via le microservice IA
    // ─────────────────────────────────────────────────────────────

    public function analyze(MessageSwift $message): array
    {
        $iaResult = $this->callAiService($message);

        if ($iaResult === null) {
            Log::warning("AnomalyService : service IA indisponible, message #{$message->id} ignoré");

            return [
                'score' => 0,
                'niveau_risque' => 'LOW',
                'raisons' => [],
                'source' => 'IA_UNAVAILABLE',
            ];
        }

        // score retourné en 0.0-1.0 → on convertit en 0-100
        $score = (int) round($iaResult['score'] * 100);
        $score = min($score, 100);
        $niveau = match (true) {
            $score >= 60 => 'HIGH',
            $score >= 20 => 'MEDIUM',
            default => 'LOW',
        };
        // reasons[].rule → tableau de raisons
        $raisons = array_column($iaResult['reasons'] ?? [], 'rule');

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
            'via_ia' => true,
            'source' => 'IA',
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Appel POST /api/predict vers swift-IA
    // ─────────────────────────────────────────────────────────────

    private function callAiService(MessageSwift $m): ?array
    {
        try {
            $createdAt = $m->CREATED_AT ?? $m->created_at ?? null;
            $senderBic = $m->SENDER_BIC ?? $m->sender_bic ?? '';
            $receiverBic = $m->RECEIVER_BIC ?? $m->receiver_bic ?? '';

            $payload = [
                'id' => $m->id,
                'type_message' => $m->TYPE_MESSAGE ?? $m->type_message ?? null,
                'direction' => $m->DIRECTION ?? $m->direction ?? 'OUT',
                'sender_bic' => $senderBic ?: null,
                'receiver_bic' => $receiverBic ?: null,
                'sender_name' => $m->SENDER_NAME ?? $m->sender_name ?? null,
                'receiver_name' => $m->RECEIVER_NAME ?? $m->receiver_name ?? null,
                'amount' => (float) ($m->AMOUNT ?? $m->amount ?? 0),
                'currency' => $m->CURRENCY ?? $m->currency ?? 'EUR',
                'status' => $m->STATUS ?? $m->status ?? null,
                'reference' => $m->REFERENCE ?? $m->reference ?? null,
                'category' => $m->CATEGORIE ?? $m->category ?? null,
                'translation_errors' => $m->TRANSLATION_ERRORS ?? $m->translation_errors ?? null,
                'created_at' => $createdAt ? (string) $createdAt : null,
                'sender_country' => $senderBic ? substr($senderBic, 4, 2) : null,
                'receiver_country' => $receiverBic ? substr($receiverBic, 4, 2) : null,
            ];

            $response = Http::timeout($this->aiTimeout)
                ->post("{$this->aiUrl}/api/predict", $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("AnomalyService IA non-2xx #{$m->id}: ".$response->status());

            return null;
        } catch (\Throwable $e) {
            Log::debug("AnomalyService IA indisponible #{$m->id}: {$e->getMessage()}");

            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Analyse en masse de tous les messages
    // ─────────────────────────────────────────────────────────────

    public function analyzeAll(): void
    {
        MessageSwift::chunk(100, function ($messages) {
            foreach ($messages as $message) {
                try {
                    $this->analyze($message);
                } catch (\Throwable $e) {
                    Log::warning("Analyse échouée #{$message->id} : {$e->getMessage()}");
                }
            }
        });
    }
}
