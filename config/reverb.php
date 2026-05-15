<?php

return [

    'default' => 'reverb',

    'servers' => [

        'reverb' => [
            'host'             => env('REVERB_SERVER_HOST', '0.0.0.0'),
            'port'             => env('REVERB_SERVER_PORT', env('REVERB_PORT', 8080)),
            'hostname'         => env('REVERB_HOST', 'localhost'),
            'scheme'           => env('REVERB_SERVER_SCHEME', 'http'),
            'max_request_size' => env('REVERB_APP_MAX_MESSAGE_SIZE', 10_000),
            'options' => [
                'tls' => [],
            ],
            'scaling' => [
                'enabled' => env('REVERB_SCALING_ENABLED', false),
                'channel' => env('REVERB_SCALING_CHANNEL', 'reverb'),
                'server'  => [
                    'url'      => env('REDIS_URL'),
                    'host'     => env('REDIS_HOST', '127.0.0.1'),
                    'port'     => env('REDIS_PORT', '6379'),
                    'username' => env('REDIS_USERNAME'),
                    'password' => env('REDIS_PASSWORD'),
                    'database' => env('REDIS_DB', '0'),
                ],
            ],
            'pulse_ingest_interval'     => env('REVERB_PULSE_INGEST_INTERVAL', 15),
            'telescope_ingest_interval' => env('REVERB_TELESCOPE_INGEST_INTERVAL', 15),
        ],
    ],

    'apps' => [
        'provider' => 'config',
        'apps' => [
            [
                'key'             => env('REVERB_APP_KEY', 'btl-reverb-key'),
                'secret'          => env('REVERB_APP_SECRET', 'btl-reverb-secret'),
                'app_id'          => env('REVERB_APP_ID', 'btl-swift'),
                'allowed_origins' => ['*'],
                'ping_interval'   => env('REVERB_APP_PING_INTERVAL', 60),
                'ping_timeout'    => env('REVERB_APP_PING_TIMEOUT', 10),
                'activity_timeout'=> env('REVERB_APP_ACTIVITY_TIMEOUT', 30),
                'max_message_size'=> env('REVERB_APP_MAX_MESSAGE_SIZE', 10000),
            ],
        ],
    ],

    'queue_connection' => env('REVERB_QUEUE_CONNECTION', 'sync'),

    'scaling' => [
        'enabled' => env('REVERB_SCALING_ENABLED', false),
        'channel' => env('REVERB_SCALING_CHANNEL', 'reverb'),
        'server'  => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'port'     => env('REDIS_PORT', '6379'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'database' => env('REDIS_DB', '0'),
        ],
    ],

    'pulse' => [
        'enabled' => env('REVERB_PULSE_ENABLED', false),
    ],
];
