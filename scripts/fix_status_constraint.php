<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Drop old restrictive constraints and keep only STATUS_CHECK
$constraints = ['CHK_MS_STATUS', 'SYS_C008603'];

foreach ($constraints as $name) {
    try {
        DB::statement("ALTER TABLE MESSAGES_SWIFT DROP CONSTRAINT {$name}");
        echo "Dropped: {$name}" . PHP_EOL;
    } catch (\Throwable $e) {
        echo "Skip {$name}: " . $e->getMessage() . PHP_EOL;
    }
}

// Verify remaining constraints
$rows = DB::select("SELECT CONSTRAINT_NAME, SEARCH_CONDITION FROM USER_CONSTRAINTS WHERE TABLE_NAME = 'MESSAGES_SWIFT' AND CONSTRAINT_TYPE = 'C' AND SEARCH_CONDITION LIKE '%STATUS%'");
echo PHP_EOL . "Remaining STATUS constraints:" . PHP_EOL;
foreach ($rows as $r) {
    echo "  {$r->constraint_name}: {$r->search_condition}" . PHP_EOL;
}
