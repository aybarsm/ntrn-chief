<?php

return (\Phar::running(false) ? [] : [
    'build' => [
        'chmod' => '0775',
        'overwrite' => false,
        'app_version' => config_path('app_version'),
        'spc' => [
            'path' => base_path('builds/utils/spc'),
            'url' => 'https://dl.static-php.dev/static-php-cli',
            'fileNamePattern' => '/(\/?)(?<fileName>[^\/]+)\.(zip|tar|tar.gz)$/',
            'distributions' => [
                'linux-x86_64' => 'bulk/php-8.3.13-micro-linux-x86_64.tar.gz',
                'linux-aarch64' => 'bulk/php-8.3.13-micro-linux-aarch64.tar.gz',
                'macos-aarch64' => 'bulk/php-8.3.9-micro-macos-aarch64.tar.gz',
                'macos-x86_64' => 'bulk/php-8.3.9-micro-macos-x86_64.tar.gz',
                'windows' => 'windows/spc-max/php-8.3.9-micro-win.zip'
//              'macos-aarch64' => 'php-8.3.9-minimal-common-macos-aarch64.sfx',
//              'macos-aarch64' => 'php-8.3.9-minimal-micro-macos-aarch64.sfx',
//              'linux-x86_64' => 'php-8.3.13-micro-linux-x86_64.sfx',
//              'linux-aarch64' => 'php-8.3.13-micro-linux-aarch64.sfx',
//              'macos-aarch64' => 'php-8.3.13-micro-macos-aarch64.sfx',
            ],
        ],

    ]
]);
