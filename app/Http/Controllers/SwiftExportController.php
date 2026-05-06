<?php

namespace App\Http\Controllers;

use App\Models\AnomalySwift;
use App\Models\MessageSwift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SwiftExportController extends Controller
{
    /**
     * Exporte MESSAGES_SWIFT + ANOMALIES_SWIFT pour réentraîner le modèle IA.
     *
     * Appelé par le microservice Python :
     *   GET http://btl_swift_app:8000/api/swift-export
     *
     * Protégé par X-API-Key (configurable via LARAVEL_AI_EXPORT_KEY dans .env).
     */
    public function export(Request $request): JsonResponse
    {
        // Vérification clé API (optionnelle si LARAVEL_AI_EXPORT_KEY est vide)
        $apiKey = config('services.anomaly_ai.export_key', '');
        if ($apiKey && $request->header('X-API-Key') !== $apiKey) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $messages = MessageSwift::with('anomaly')
                ->orderBy('ID', 'desc')
                ->limit(50000)
                ->get()
                ->map(function ($msg) {
                    $anomaly = $msg->anomaly;
                    $senderBic   = $msg->SENDER_BIC   ?? $msg->sender_bic   ?? '';
                    $receiverBic = $msg->RECEIVER_BIC ?? $msg->receiver_bic ?? '';

                    return [
                        'id'               => $msg->ID ?? $msg->id,
                        'type_message'     => $msg->TYPE_MESSAGE ?? $msg->type_message,
                        'direction'        => $msg->DIRECTION ?? $msg->direction ?? 'IN',
                        'sender_bic'       => $senderBic ?: null,
                        'receiver_bic'     => $receiverBic ?: null,
                        'sender_name'      => $msg->SENDER_NAME ?? $msg->sender_name,
                        'receiver_name'    => $msg->RECEIVER_NAME ?? $msg->receiver_name,
                        'amount'           => (float) ($msg->AMOUNT ?? $msg->amount ?? 0),
                        'currency'         => $msg->CURRENCY ?? $msg->currency ?? 'EUR',
                        'value_date'       => $msg->VALUE_DATE ?? $msg->value_date,
                        'created_at'       => $msg->CREATED_AT ?? $msg->created_at,
                        'reference'        => $msg->REFERENCE ?? $msg->reference,
                        'status'           => $msg->STATUS ?? $msg->status,
                        'translation_errors' => $msg->TRANSLATION_ERRORS ?? $msg->translation_errors,
                        'category'         => $msg->CATEGORIE ?? $msg->category,
                        'sender_country'   => $senderBic ? substr($senderBic, 4, 2) : null,
                        'receiver_country' => $receiverBic ? substr($receiverBic, 4, 2) : null,
                        // Labels depuis ANOMALIES_SWIFT
                        'anomaly_score'    => $anomaly ? (int) $anomaly->score : null,
                        'is_anomalie'      => $anomaly ? (int) ($anomaly->score >= 20) : null,
                        'niveau_risque'    => $anomaly ? $anomaly->niveau_risque : null,
                    ];
                });

            return response()->json([
                'count'    => $messages->count(),
                'messages' => $messages,
            ]);

        } catch (\Throwable $e) {
            Log::error('SwiftExportController: '.$e->getMessage());
            return response()->json(['error' => 'Export failed: '.$e->getMessage()], 500);
        }
    }
}
