<?php

declare(strict_types=1);

use App\Services\Helper;

use function Illuminate\Filesystem\join_paths;

return Helper::isPhar() ? [] : [
    'temp' => join_paths(sys_get_temp_dir(), 'ntrn-chief_dev'),
    'github' => [
        'owner' => env('DEV_GITHUB_OWNER', 'aybarsm'),
        'repo' => env('DEV_GITHUB_REPO', 'ntrn-chief'),
        'token' => env('DEV_GITHUB_TOKEN'),
        'http' => [
            'headers' => Helper::jsonDecode(env('DEV_GITHUB_HTTP_HEADERS'), []),
            'timeout' => (int) env('DEV_GITHUB_HTTP_TIMEOUT', 60),
        ],
    ],
    'build' => [
        'path' => join_paths(base_path(), 'builds'),
        'chmod' => '0755',
        'backup' => [
            'path' => join_paths(base_path(), 'builds', 'backups'),
        ],
        'exclude' => [
            join_paths(base_path(), 'app', 'Commands', 'Dev'),
            config_path('dev.php'),
        ],
        'micro' => [
            'spc' => join_paths(base_path(), 'builds', 'utils', 'spc', 'darwin-aarch64'),
            'path' => join_paths(base_path(), 'builds', 'utils', 'micro'),
            'url' => 'https://dl.static-php.dev/static-php-cli',
            'archivePattern' => '/\.(zip|tar|tar\.gz)$/',
            'distributions' => [
                'linux-x86_64' => [
                    'binary' => 'ntrn_linux-x86_64',
                    'os' => 'linux',
                    'arch' => 'x86_64',
                    'local' => 'php-8.3.12-bulk-micro-linux-x86_64.sfx',
                    'remote' => 'bulk/php-8.3.12-micro-linux-x86_64.tar.gz',
                    'archiveFile' => 'micro.sfx',
                    'md5sum' => true,
                ],
                'linux-aarch64' => [
                    'binary' => 'ntrn_linux-aarch64',
                    'os' => 'linux',
                    'arch' => 'aarch64',
                    'local' => 'php-8.3.12-bulk-micro-linux-aarch64.sfx',
                    'remote' => 'bulk/php-8.3.12-micro-linux-aarch64.tar.gz',
                    'archiveFile' => 'micro.sfx',
                    'md5sum' => true,
                ],
                'darwin-aarch64' => [
                    'binary' => 'ntrn_darwin-aarch64',
                    'os' => 'darwin',
                    'arch' => 'aarch64',
                    'local' => 'php-8.3.12-common-micro-macos-aarch64.sfx',
                    'remote' => 'bulk/php-8.3.12-micro-macos-aarch64.tar.gz',
                    'archiveFile' => 'micro.sfx',
                    'md5sum' => true,
                ],
                'darwin-x86_64' => [
                    'binary' => 'ntrn_darwin-x86_64',
                    'os' => 'darwin',
                    'arch' => 'x86_64',
                    'local' => 'php-8.3.12-bulk-micro-macos-x86_64.sfx',
                    'remote' => 'bulk/php-8.3.12-micro-macos-x86_64.tar.gz',
                    'archiveFile' => 'micro.sfx',
                    'md5sum' => true,
                ],
            ],
        ],
    ],
];
