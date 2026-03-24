<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Jobs\ProcessSwiftFileJob;

$path = storage_path('app/temp/test_pacs008.xml');
if (!file_exists($path)) {
    echo "Test XML not found: $path\n";
    exit(1);
}

// Run the job synchronously to see immediate result
ProcessSwiftFileJob::dispatchSync($path, 1);

echo "ProcessSwiftFileJob::dispatchSync executed for $path\n";
