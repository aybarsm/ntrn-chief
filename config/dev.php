<?php

use App\Services\Helper;

use function Illuminate\Filesystem\join_paths;

return Helper::isPhar() ? [] : [
    'temp' => join_paths(sys_get_temp_dir(), 'ntrn-chief_dev'),
    'git' => [
        'token' => env('DEV_GIT_TOKEN'),
    ],
    'build' => [
        'path' => join_paths(base_path(), 'builds'),
        'chmod' => '0755',
        'app_version' => config_path('app_version'),
        'app_build' => config_path('app_build'),
        'backup' => [
            'path' => join_paths(base_path(), 'builds', 'backups'),
        ],
        'exclude' => [
            join_paths(base_path(), 'app', 'Commands', 'Dev'),
            config_path('dev.php'),
        ],
        'micro' => [
            'path' => join_paths(base_path(), 'builds', 'utils', 'micro'),
            'url' => 'https://dl.static-php.dev/static-php-cli',
            'archivePattern' => '/\.(zip|tar|tar\.gz)$/',
            'distributions' => [
                'linux-x86_64' => [
                    'local' => 'php-8.3.12-bulk-micro-linux-x86_64.sfx',
                    'remote' => 'bulk/php-8.3.12-micro-linux-x86_64.tar.gz',
                    'archiveFile' => 'micro.sfx',
                ],
                'linux-aarch64' => [
                    'local' => 'php-8.3.12-bulk-micro-linux-aarch64.sfx',
                    'remote' => 'bulk/php-8.3.12-micro-linux-aarch64.tar.gz',
                    'archiveFile' => 'micro.sfx',
                ],
                'darwin-aarch64' => [
                    'local' => 'php-8.3.12-common-micro-macos-aarch64.sfx',
                    'remote' => 'bulk/php-8.3.12-micro-macos-aarch64.tar.gz',
                    'archiveFile' => 'micro.sfx',
                ],
                'darwin-x86_64' => [
                    'local' => 'php-8.3.12-bulk-micro-macos-x86_64.sfx',
                    'remote' => 'bulk/php-8.3.12-micro-macos-x86_64.tar.gz',
                    'archiveFile' => 'micro.sfx',
                ],
            ],
        ],
    ],
];
