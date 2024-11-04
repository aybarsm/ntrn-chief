<?php

declare(strict_types=1);

return [
    'default' => 'main',
    'connections' => [
        'dev' => [
            'method'     => 'token',
            'token'      => 'your-token',
            // 'backoff'    => false,
            // 'cache'      => false,
            // 'version'    => 'v3',
            // 'enterprise' => false,
        ],
        'update' => [
            'method'     => 'token',
            'token'      => 'your-token',
            // 'backoff'    => false,
            // 'cache'      => false,
            // 'version'    => 'v3',
            // 'enterprise' => false,
        ],
        'main' => [
            'method'     => 'token',
            'token'      => 'your-token',
            // 'backoff'    => false,
            // 'cache'      => false,
            // 'version'    => 'v3',
            // 'enterprise' => false,
        ],
        'app' => [
            'method'       => 'application',
            'clientId'     => 'your-client-id',
            'clientSecret' => 'your-client-secret',
            // 'backoff'      => false,
            // 'cache'        => false,
            // 'version'      => 'v3',
            // 'enterprise'   => false,
        ],
        'jwt' => [
            'method'       => 'jwt',
            'token'        => 'your-jwt-token',
            // 'backoff'      => false,
            // 'cache'        => false,
            // 'version'      => 'v3',
            // 'enterprise'   => false,
        ],
        'private' => [
            'method'     => 'private',
            'appId'      => 'your-github-app-id',
            'keyPath'    => 'your-private-key-path',
            // 'key'        => 'your-private-key-content',
            // 'passphrase' => 'your-private-key-passphrase'
            // 'backoff'    => false,
            // 'cache'      => false,
            // 'version'    => 'v3',
            // 'enterprise' => false,
        ],
        'none' => [
            'method'     => 'none',
            // 'backoff'    => false,
            // 'cache'      => false,
            // 'version'    => 'v3',
            // 'enterprise' => false,
        ],
    ],
    'cache' => [
        'main' => [
            'driver'    => 'illuminate',
            'connector' => null, // null means use default driver
            // 'min'       => 43200,
            // 'max'       => 172800
        ],
        'bar' => [
            'driver'    => 'illuminate',
            'connector' => 'redis', // config/cache.php
            // 'min'       => 43200,
            // 'max'       => 172800
        ],
    ],
];
