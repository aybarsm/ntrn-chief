<?php

declare(strict_types=1);

namespace App\Commands;

use App\Framework\Commands\Command;
use App\Services\Helper;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class AppInfo extends Command
{
    protected $signature = 'app:info
    {--format=txt : The output format}
    {--php-info : Display PHP information}';

    protected $description = 'Display application information';

    public function handle(): void
    {
        if ($this->option('php-info')) {
            $phpinfo = Helper::phpinfo();
            $this->line($phpinfo);

            return;
        }
        $data = [
            'name' => $this->getApplication()->getName(),
            'version' => $this->getApplication()->getVersion(),
            'cfg' => [
                'PUID' => env_or_cfg('PUID', ''),
                'PGID' => env_or_cfg('PGID', ''),
            ],
            'constant' => [
                'NTRN_PUID' => get_constant('NTRN_PUID', ''),
                'NTRN_PGID' => get_constant('NTRN_PGID', ''),
            ],
            'os' => [
                'windows' => PHP_OS_FAMILY === 'Windows',
                'linux' => PHP_OS_FAMILY === 'Linux',
                'darwin' => PHP_OS_FAMILY === 'Darwin',
                'uname' => [
                    'a' => php_uname('a'),
                    's' => php_uname('s'),
                    'n' => php_uname('n'),
                    'r' => php_uname('r'),
                    'v' => php_uname('v'),
                    'm' => php_uname('m'),
                ],
                'ros' => Helper::appIsRos(),
            ],
        ];

        if (Helper::appHasPosix()) {
            $data['os']['posix'] = [
                'cwd' => posix_getcwd(),
                'egid' => posix_getegid(),
                'euid' => posix_geteuid(),
                'gid' => posix_getgid(),
                'groups' => posix_getgroups(),
                'login' => posix_getlogin(),
                'pgrp' => posix_getpgrp(),
                'pid' => posix_getpid(),
                'ppid' => posix_getppid(),
                'uid' => posix_getuid(),
                'times' => posix_times(),
                'uname' => posix_uname(),
            ];
        }

        $data['php'] = [
            'sapi_name' => php_sapi_name(),
            'ini' => ini_get_all(),
            'env' => $_ENV,
            'server' => $_SERVER,
        ];

        if (in_array($this->option('format'), ['json', 'json_pretty'])) {
            $this->line(json_encode(Arr::undot($data), $this->option('format') === 'json_pretty' ? JSON_PRETTY_PRINT : 0));

            return;
        }

        foreach (Arr::dot(Arr::undot($data)) as $key => $value) {
            $keyVisual = Str::replace('.', ' -> ', $key);
            $valVisual = $value === true ? 'Yes' : ($value === false ? 'No' : $value);
            //            $valVisual = truthy($value) ? 'Yes' : (falsy($value) ? 'No' : $value);
            $this->line("<info>{$keyVisual}</info> : <comment>{$valVisual}</comment>");
        }
    }

    public function getOptions(): array
    {
        return [
            ['format', null, InputOption::VALUE_OPTIONAL, 'The output format', 'txt', ['txt', 'json', 'json_pretty']],
        ];
    }
}
