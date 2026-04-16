<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = DB::select("SELECT CONSTRAINT_NAME, SEARCH_CONDITION FROM USER_CONSTRAINTS WHERE TABLE_NAME = 'MESSAGES_SWIFT' AND CONSTRAINT_TYPE = 'C'");
foreach ($rows as $r) {
    echo $r->constraint_name . ': ' . $r->search_condition . PHP_EOL;
}
