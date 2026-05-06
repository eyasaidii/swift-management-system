<?php

namespace App\Jobs;

use App\Models\MessageSwift;
use App\Services\AnomalyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * AnalyzeAnomalyJob
 *
 * Job asynchrone déclenché automatiquement par MessageSwiftObserver
 * lorsqu'un message SWIFT émis reçoit ses données financières.
 *
 * Pour les messages REÇUS (IN), l'analyse est déjà faite dans ProcessSwiftFileJob.
 * Ce job couvre les messages ÉMIS (OUT) créés via le formulaire web.
 */
class AnalyzeAnomalyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public int $backoff = 10;

    public function __construct(protected int $messageId) {}

    public function handle(): void
    {
        $message = MessageSwift::find($this->messageId);

        if (! $message) {
            Log::warning("AnalyzeAnomalyJob : message #{$this->messageId} introuvable");

            return;
        }

        try {
            $result = app(AnomalyService::class)->analyze($message->fresh());

            Log::info('Analyse IA automatique (Observer)', [
                'message_id' => $message->id,
                'reference' => $message->REFERENCE,
                'score' => $result['score'],
                'niveau' => $result['niveau_risque'],
                'via_ia' => $result['via_ia'] ?? true,
            ]);
        } catch (Throwable $e) {
            Log::warning('AnalyzeAnomalyJob : analyse échouée (non bloquant)', [
                'message_id' => $this->messageId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
