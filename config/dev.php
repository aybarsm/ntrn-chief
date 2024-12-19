<?php

declare(strict_types=1);

use App\Services\Helper;
use Illuminate\Console\Application;

return Helper::isPhar() ? [] : [
    'temp' => joinPaths(sys_get_temp_dir(), 'ntrn-chief_dev'),
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
        'path' => joinBasePath('builds'),
        'infoFile' => joinBasePath('build.json'),
        'phar' => str_replace(["'", '"'], '', Application::artisanBinary()).'.phar',
        'ts' => Helper::tsSafe(),
        'id_pattern' => '/^(?P<appVer>v(\d+)\.(\d+)\.(\d+))-(?P<tsSafe>(\d{4})(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])T([01][0-9]|2[0-3])([0-5][0-9])([0-5][0-9])Z)$/',
        'chmod' => '0755',
        'box' => [
            'working-dir' => base_path(),
            'config' => joinBasePath('box.json'),
            'binary' => joinBasePath('vendor', 'laravel-zero', 'framework', 'bin', (windows_os() ? 'box.bat' : 'box')),
        ],
        'backup' => [
            'path' => joinBasePath('builds', 'backups'),
            'historyFile' => joinBasePath('builds', 'backups', 'history.json'),
        ],
        'exclude' => [
            joinBasePath('app', 'Commands', 'Dev'),
            config_path('dev.php'),
        ],
        'spc' => [
            'local' => joinBasePath('builds', 'utils', 'spc', 'spc-bin', '2.4.2_macos-aarch64'),
            'args' => [
                'debug',
                'no-interaction',
                'with-ini-set="phar.readonly=Off"',
            ],
            'chmod' => '0755',
            'remote' => [
                'url' => 'https://github.com/crazywhalecc/static-php-cli/releases/download/2.4.2/spc-macos-aarch64.tar.gz',
                'saveAs' => joinBasePath('builds', 'utils', 'spc', 'spc-bin', '2.4.2_spc-macos-aarch64.tar.gz'),
                'archive' => true,
                'archiveFile' => 'spc',
            ],
        ],
        'static' => [
            [
                'binary' => 'ntrn_linux-x86_64',
                'os' => 'linux',
                'arch' => 'x86_64',
                'local' => joinBasePath('builds', 'utils', 'spc', 'bulk', 'php-8.3.14-micro-linux-x86_64.sfx'),
                'chmod' => '0755',
                'remote' => [
                    'url' => 'https://dl.static-php.dev/static-php-cli/bulk/php-8.3.14-micro-linux-x86_64.tar.gz',
                    'saveAs' => joinBasePath('builds', 'utils', 'spc', 'bulk', 'php-8.3.14-micro-linux-x86_64.tar.gz'),
                    'archive' => true,
                    'archiveFile' => 'micro.sfx',
                ],
                'sanityCheck' => 'docker run --rm -it --platform linux/amd64 -v "{{BASE_PATH}}/dev:/data/dev" -v "{{BINARY}}:/data/ntrn" -e NTRN_BASE="/data/dev" -w /data debian:bookworm-slim /usr/bin/bash -c "chmod +x /data/ntrn; /data/ntrn list"',
            ],
            [
                'binary' => 'ntrn_linux-aarch64',
                'os' => 'linux',
                'arch' => 'aarch64',
                'local' => joinBasePath('builds', 'utils', 'spc', 'bulk', 'php-8.3.14-micro-linux-aarch64.sfx'),
                'chmod' => '0755',
                'remote' => [
                    'url' => 'https://dl.static-php.dev/static-php-cli/bulk/php-8.3.14-micro-linux-aarch64.tar.gz',
                    'saveAs' => joinBasePath('builds', 'utils', 'spc', 'bulk', 'php-8.3.14-micro-linux-aarch64.tar.gz'),
                    'archive' => true,
                    'archiveFile' => 'micro.sfx',
                ],
                'sanityCheck' => 'docker run --rm -it -v "{{BASE_PATH}}/dev:/data/dev" -v "{{BINARY}}:/data/ntrn" -e NTRN_BASE="/data/dev" -w /data debian:bookworm /usr/bin/bash -c "chmod +x /data/ntrn; /data/ntrn list"',
            ],
            [
                'binary' => 'ntrn_darwin-aarch64',
                'os' => 'darwin',
                'arch' => 'aarch64',
                'local' => joinBasePath('builds', 'utils', 'spc', 'bulk', 'php-8.3.14-micro-macos-aarch64.sfx'),
                'chmod' => '0755',
                'remote' => [
                    'url' => 'https://dl.static-php.dev/static-php-cli/bulk/php-8.3.14-micro-macos-aarch64.tar.gz',
                    'saveAs' => joinBasePath('builds', 'utils', 'spc', 'bulk', 'php-8.3.14-micro-macos-aarch64.tar.gz'),
                    'archive' => true,
                    'archiveFile' => 'micro.sfx',
                ],
                'sanityCheck' => '{{BINARY}} list',
            ],
            [
                'binary' => 'ntrn_darwin-x86_64',
                'os' => 'darwin',
                'arch' => 'x86_64',
                'local' => joinBasePath('builds', 'utils', 'spc', 'bulk', 'php-8.3.14-micro-macos-x86_64.sfx'),
                'chmod' => '0755',
                'remote' => [
                    'url' => 'https://dl.static-php.dev/static-php-cli/bulk/php-8.3.14-micro-macos-x86_64.tar.gz',
                    'saveAs' => joinBasePath('builds', 'utils', 'spc', 'bulk', 'php-8.3.14-micro-macos-x86_64.tar.gz'),
                    'archive' => true,
                    'archiveFile' => 'micro.sfx',
                ],
            ],
        ],
    ],

];
