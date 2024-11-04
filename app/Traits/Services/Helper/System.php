<?php

namespace App\Traits\Services\Helper;

use Illuminate\Support\Str;
use Symfony\Component\Process\Process as SymfonyProcess;

trait System
{
    protected static array $system = [];

    public static function systemOs(): string
    {
        if (! array_key_exists('os', static::$system)) {
            static::$system['os'] = static::buildInfo('os', Str::lower(PHP_OS_FAMILY));
        }

        return static::$system['os'];
    }

    public static function systemArch(): string
    {
        if (! array_key_exists('arch', static::$system)) {
            static::$system['arch'] = static::buildInfo('arch', function () {
                $cmd = match (static::systemOs()) {
                    'linux', 'darwin' => 'uname -m',
                    'windows' => 'echo %PROCESSOR_ARCHITECTURE%',
                    default => null,
                };

                if ($cmd === null) {
                    return '';
                }

                try {
                    $process = SymfonyProcess::fromShellCommandline($cmd)->enableOutput()->mustRun();
                } catch (\Exception $e) {
                    $process = null;
                }

                if ($process === null) {
                    return '';
                }

                $output = $process->isSuccessful() ? static::firstLine($process->getOutput(), true) : null;

                return match ($output) {
                    'x86_64', 'amd64' => 'x86_64',
                    'aarch64', 'arm64' => 'aarch64',
                    default => ''
                };
            });
        }

        return static::$system['arch'];
    }

    public static function systemDist(mixed $default = null): mixed
    {
        if (! array_key_exists('dist', static::$system)) {
            static::$system['dist'] = static::buildInfo('dist', static::systemOs().(blank(static::systemArch()) ? '' : '-'.static::systemArch()));
        }

        return static::$system['dist'];
    }
}
