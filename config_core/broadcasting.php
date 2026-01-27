<?php

return [

    'default' => getenv('BROADCAST_DRIVER') ?: 'null',

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => getenv('PUSHER_APP_KEY') ?: null,
            'secret' => getenv('PUSHER_APP_SECRET') ?: null,
            'app_id' => getenv('PUSHER_APP_ID') ?: null,
            'options' => [
                'cluster' => getenv('PUSHER_APP_CLUSTER') ?: null,
                'useTLS' => true,
                'encrypted' => true,
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
