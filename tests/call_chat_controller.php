<?php
$app = require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request as HttpRequest;

// Try to login user id 1 (adjust if needed)
if (!class_exists('App\\Models\\User')) {
    echo "User model not found\n"; exit(1);
}

try {
    Auth::loginUsingId(1);
} catch (Exception $e) {
    // ignore
}

$payload = ['question' => 'E2E test via controller', 'history' => []];
$request = HttpRequest::create('/chat-global', 'POST', $payload);
// Bind the resolved user for auth()->user()
$request->setUserResolver(function () { return Auth::user(); });

$controller = new App\Http\Controllers\SwiftController();
$response = $controller->chatGlobal($request);

if (is_object($response)) {
    if (method_exists($response, 'getContent')) {
        echo $response->getContent();
    } else {
        var_export($response);
    }
} else {
    echo (string)$response;
}
