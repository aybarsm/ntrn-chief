<?php

namespace App\Traits\Services\Helper;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process as SymfonyProcess;

trait System
{
    protected static array $system = [];

    public static function systemOs(): string
    {
        if (! Arr::has(static::$system, 'osFamily')) {
            static::$system['osFamily'] = Str::lower(PHP_OS_FAMILY);
        }

        return static::$system['osFamily'];
    }

    public static function systemArch(): string
    {
        if (! Arr::has(static::$system, 'arch')) {
            $cmd = match (static::systemOs()) {
                'linux', 'darwin' => 'uname -m',
                'windows' => 'echo %PROCESSOR_ARCHITECTURE%',
                default => null,
            };

            if ($cmd === null){
                static::$system['arch'] = '';
                return static::$system['arch'];
            }

            try {
                $process = SymfonyProcess::fromShellCommandline($cmd)->enableOutput()->mustRun();
            } catch (\Exception $e) {
                $process = null;
            }

            if ($process === null) {
                static::$system['arch'] = '';
                return static::$system['arch'];
            }

            $output = $process->isSuccessful() ? static::firstLine($process->getOutput(), true) : null;
            static::$system['arch'] = match ($output) {
                'x86_64', 'amd64' => 'x86_64',
                'aarch64', 'arm64' => 'aarch64',
                default => ''
            };
        }

        return static::$system['arch'];
    }

    public static function systemDist(mixed $default = null): mixed
    {
        if (! Arr::has(static::$system, 'dist')) {
            static::$system['dist'] = static::systemOs() . (blank(static::systemArch()) ? '' : '-' . static::systemArch());
        }

        return static::$system['dist'];
    }

    protected static array $buildConf;
    public static function buildInfo(string $key, mixed $default = null): mixed
    {
        if (! isset(static::$buildConf)) {
            static::$buildConf = File::exists(base_path('build.json')) ? File::json(base_path('build.json')) : [];
        }

        return data_get(static::$buildConf, $key, $default);
    }

    public static function buildVersion(mixed $default = null): mixed
    {
        return static::buildInfo('version', $default);
    }

    public static function buildArch(mixed $default = null): mixed
    {
        return static::buildInfo('arch', $default);
    }

    public static function buildTime(mixed $default = null): mixed
    {
        return static::buildInfo('build', $default);
    }

    public static function buildDist(mixed $default = null): mixed
    {
        return static::buildInfo('dist', $default);
    }

}
