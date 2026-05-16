<?php
require __DIR__ . '/../vendor/autoload.php';

$client = new \GuzzleHttp\Client(['timeout' => 45]);
$payload = [
    'question' => 'test from Laravel app',
    'role' => 'swift-manager',
    'stats' => ['total' => 0],
    'history' => []
];

try {
    $resp = $client->post('http://python-api:8001/api/chat-global', [
        'json' => $payload,
        'headers' => ['Accept' => 'application/json']
    ]);

    echo $resp->getBody()->getContents();
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
