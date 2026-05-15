<?php

return [

    'default' => env('BROADCAST_CONNECTION', 'reverb'),

    'connections' => [

        'reverb' => [
            'driver'   => 'reverb',
            'key'      => env('REVERB_APP_KEY', 'btl-reverb-key'),
            'secret'   => env('REVERB_APP_SECRET', 'btl-reverb-secret'),
            'app_id'   => env('REVERB_APP_ID', 'btl-swift'),
            'options'  => [
                'host'   => env('REVERB_HOST', '127.0.0.1'),
                'port'   => env('REVERB_PORT', 8080),
                'scheme' => env('REVERB_SCHEME', 'http'),
                'useTLS' => env('REVERB_SCHEME', 'http') === 'https',
            ],
            'client_options' => [],
        ],

        'pusher' => [
            'driver' => 'pusher',
            'key'    => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
                'useTLS'  => true,
            ],
        ],

        'log'  => ['driver' => 'log'],
        'null' => ['driver' => 'null'],
    ],

];
