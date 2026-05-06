<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes (Scheduled Commands)
|--------------------------------------------------------------------------
*/

// Réentraînement automatique du modèle IA chaque nuit à 2h00
// (utilise les vraies données Oracle : MESSAGES_SWIFT + ANOMALIES_SWIFT)
Schedule::command('retrain:model --sync')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/retrain.log'));
