<?php

declare(strict_types=1);

use App\Services\Helper;

use Illuminate\Console\Application;
use function Illuminate\Filesystem\join_paths;

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
        'phar' => str_replace(["'", '"'], '', Application::artisanBinary()) . '.phar',
        'ts' => Helper::tsSafe(),
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
            'local' => joinBasePath('builds', 'utils', 'spc', 'spc-bin', '2.3.6_macos-aarch64'),
            'chmod' => '0755',
            'remote' => [
                'url' => 'https://github.com/crazywhalecc/static-php-cli/releases/download/2.3.6/spc-macos-aarch64.tar.gz',
                'saveAs' => joinBasePath('builds', 'utils', 'spc', 'spc-bin', '2.3.6_spc-macos-aarch64.tar.gz'),
                'archive' => true,
                'archiveFile' => 'spc',
            ],
        ],
        'static' => [
            [
                'binary' => 'ntrn_linux-x86_64',
                'os' => 'linux',
                'arch' => 'x86_64',
                'local' => joinBasePath('builds', 'utils', 'spc', 'bulk', 'php-8.3.13-micro-linux-x86_64.sfx'),
                'md5sum' => true,
                'remote' => [
                    'url' => 'https://dl.static-php.dev/static-php-cli/bulk/php-8.3.13-micro-linux-x86_64.tar.gz',
                    'saveAs' => joinBasePath('builds', 'utils', 'spc', 'bulk', 'php-8.3.13-micro-linux-x86_64.tar.gz'),
                    'archive' => true,
                    'archiveFile' => 'micro.sfx',
                ],
            ],
            [
                'binary' => 'ntrn_linux-aarch64',
                'os' => 'linux',
                'arch' => 'aarch64',
                'local' => joinBasePath('builds', 'utils', 'spc', 'bulk', 'php-8.3.13-micro-linux-aarch64.sfx'),
                'md5sum' => true,
                'remote' => [
                    'url' => 'https://dl.static-php.dev/static-php-cli/bulk/php-8.3.13-micro-linux-aarch64.tar.gz',
                    'saveAs' => joinBasePath('builds', 'utils', 'spc', 'bulk', 'php-8.3.13-micro-linux-aarch64.tar.gz'),
                    'archive' => true,
                    'archiveFile' => 'micro.sfx',
                ],
            ],
            [
                'binary' => 'ntrn_darwin-aarch64',
                'os' => 'darwin',
                'arch' => 'aarch64',
                'local' => joinBasePath('builds', 'utils', 'spc', 'bulk', 'php-8.3.13-micro-macos-aarch64.sfx'),
                'md5sum' => true,
                'remote' => [
                    'url' => 'https://dl.static-php.dev/static-php-cli/bulk/php-8.3.13-micro-macos-aarch64.tar.gz',
                    'saveAs' => joinBasePath('builds', 'utils', 'spc', 'bulk', 'php-8.3.13-micro-macos-aarch64.tar.gz'),
                    'archive' => true,
                    'archiveFile' => 'micro.sfx',
                ],
            ],
            [
                'binary' => 'ntrn_darwin-x86_64',
                'os' => 'darwin',
                'arch' => 'x86_64',
                'local' => joinBasePath('builds', 'utils', 'spc', 'bulk', 'php-8.3.13-micro-macos-x86_64.sfx'),
                'md5sum' => true,
                'remote' => [
                    'url' => 'https://dl.static-php.dev/static-php-cli/bulk/php-8.3.13-micro-macos-x86_64.tar.gz',
                    'saveAs' => joinBasePath('builds', 'utils', 'spc', 'bulk', 'php-8.3.13-micro-macos-x86_64.tar.gz'),
                    'archive' => true,
                    'archiveFile' => 'micro.sfx',
                ],
            ],
        ],
    ],

];
