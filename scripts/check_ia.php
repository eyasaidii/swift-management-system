<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Top 20 anomalies par score desc, avec info message
$rows = \App\Models\AnomalySwift::with('message')
    ->orderByDesc('score')
    ->take(20)
    ->get();

echo str_pad('Référence', 22).str_pad('Dir', 5).str_pad('Montant', 20).str_pad('Score', 7).str_pad('Niveau', 10)."Raisons\n";
echo str_repeat('-', 100)."\n";

foreach ($rows as $a) {
    $msg = $a->message;
    $ref = $msg ? ($msg->reference ?? $msg->REFERENCE ?? '?') : '?';
    $dir = $msg ? ($msg->direction ?? $msg->DIRECTION ?? '?') : '?';
    $amt = $msg ? ($msg->amount ?? $msg->AMOUNT ?? 0) : 0;
    $cur = $msg ? ($msg->currency ?? $msg->CURRENCY ?? '') : '';
    $raisons = is_array($a->raisons) ? $a->raisons : json_decode($a->raisons ?? '[]', true);
    echo str_pad(substr($ref, 0, 20), 22)
       .str_pad($dir, 5)
       .str_pad(number_format($amt, 2).' '.$cur, 20)
       .str_pad($a->score, 7)
       .str_pad($a->niveau_risque, 10)
       .implode(', ', $raisons)
       ."\n";
}
