<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MessageSwift;

$messages = MessageSwift::orderBy('CREATED_AT', 'desc')->take(10)->get();

if ($messages->isEmpty()) {
    echo "No messages found\n";
    exit(0);
}

foreach ($messages as $m) {
    echo "ID: {$m->id} | TYPE: {$m->TYPE_MESSAGE} | DIR: {$m->DIRECTION} | CAT: {$m->CATEGORIE} | REF: {$m->REFERENCE} | AMT: {$m->AMOUNT} {$m->CURRENCY} | CREATED_BY: {$m->CREATED_BY}\n";
}
