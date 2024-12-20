<?php

declare(strict_types=1);

return [
    'default' => 'update',
    'connections' => [
        'dev' => [
            'username' => env('GITHUB_DEV_USERNAME', 'aybarsm'),
            'repo' => env('GITHUB_DEV_REPO', 'ntrn-chief'),
            'method' => env('GITHUB_DEV_METHOD', 'token'),
            'token' => env('GITHUB_DEV_TOKEN'),
        ],
        'update' => [
            'method' => env('GITHUB_UPDATE_METHOD', 'none'),
            'token' => env('GITHUB_UPDATE_TOKEN'),
        ],
        'none' => [
            'method' => 'none',
        ],
    ],
];
