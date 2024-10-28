<?php
use function Illuminate\Filesystem\join_paths;

return (\Phar::running(false) ? [] : [
    'temp' => join_paths(sys_get_temp_dir(), 'ntrn-chief_dev'),
    'build' => [
        'chmod' => '0775',
        'app_version' => config_path('app_version'),
        'micro' => [
            'path' => base_path('builds/utils/micro'),
            'url' => 'https://dl.static-php.dev/static-php-cli',
            'archivePattern' => '/\.(zip|tar|tar\.gz)$/',
            'distributions' => [
                'linux-x86_64' => [
                    'local' => 'php-8.3.12-bulk-micro-linux-x86_64.sfx',
                    'remote' => 'bulk/php-8.3.12-micro-linux-x86_64.tar.gz',
                ],
                'linux-aarch64' => [
                    'local' => 'php-8.3.12-bulk-micro-linux-aarch64.sfx',
                    'remote' => 'bulk/php-8.3.12-micro-linux-aarch64.tar.gz',
                ],
                'macos-aarch64' => [
                    'local' => 'php-8.3.12-bulk-micro-linux-aarch64.sfx',
                    'remote' => 'bulk/php-8.3.12-micro-macos-aarch64.tar.gz',
                ],
                'macos-x86_64' => [
                    'local' => 'php-8.3.12-bulk-micro-linux-aarch64.sfx',
                    'remote' => 'bulk/php-8.3.9-micro-macos-x86_64.tar.gz',
                ],
                'windows' => [
                    'local' => 'php-8.3.12-max-micro-win.sfx',
                    'remote' => 'windows/spc-max/php-8.3.12-micro-win.zip',
                ],
//              'macos-aarch64' => 'php-8.3.9-minimal-common-macos-aarch64.sfx',
//              'macos-aarch64' => 'php-8.3.9-minimal-micro-macos-aarch64.sfx',
//              'linux-x86_64' => 'php-8.3.13-micro-linux-x86_64.sfx',
//              'linux-aarch64' => 'php-8.3.13-micro-linux-aarch64.sfx',
//              'macos-aarch64' => 'php-8.3.13-micro-macos-aarch64.sfx',
            ],
        ],

    ]
]);
