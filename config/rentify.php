<?php

return [
    'locales' => ['pt', 'en'],
    'max_days' => 5,
    'gap_days' => 1,

    'admin' => [
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'demo_password' => env('DEMO_PASSWORD'),
];

