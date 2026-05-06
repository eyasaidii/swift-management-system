<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$total = \App\Models\Transaction::count();
$sw = \App\Models\Transaction::whereHas('messageSwift', function ($q) {
    $q->where('REFERENCE', 'like', 'SW%');
})->count();

echo "Total transactions:      {$total}\n";
echo "Transactions for SW*:    {$sw}\n";
