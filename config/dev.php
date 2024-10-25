<?php

return (\Phar::running(false) ? [] : [
    'build' => [
        'chmod' => 755,
        'overwrite' => false,
        'app_version' => config_path('app_version'),
        'sfx' => [
            'path' => base_path('dev/utils/sfx'),
            'url' => 'https://s3.blrm.net/vault/php-micro'
        ],
        'distributions' => [
            'linux-x86_64' => 'php-8.3.13-micro-linux-x86_64.sfx',
            'linux-aarch64' => 'php-8.3.13-micro-linux-aarch64.sfx',
            'macos-aarch64' => 'php-8.3.13-micro-macos-aarch64.sfx',
        ]
    ]
]);
