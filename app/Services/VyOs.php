<?php

namespace App\Services;

use App\Enums\VyOSConfig;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

class VyOs
{
    public static function op(string $cmd): string|false
    {
        $cmd = Str::of($cmd)->trim()->start('/opt/vyatta/bin/vyatta-op-cmd-wrapper ')->value();
        $process = Process::run($cmd);

        return $process->failed() ? false : trim($process->output());
    }

    public static function prepareCfg(string|array|Collection $commands): array
    {
        $commands = $commands instanceof Collection ? $commands->toArray() : $commands;
        $commands = Arr::wrap($commands);

        $res = ['/opt/vyatta/sbin/vyatta-cfg-cmd-wrapper begin'];

        foreach($commands as $key => $cmd) {
            $cmd = Str::of($cmd)->trim()
                ->chopStart('/opt/vyatta/sbin/vyatta-cfg-cmd-wrapper')
                ->trim()
                ->value();

            if ($cmd === 'begin' || $cmd === 'end') {
                continue;
            }

            $res[] = "/opt/vyatta/sbin/vyatta-cfg-cmd-wrapper {$cmd}";
        }

        $res[] = '/opt/vyatta/sbin/vyatta-cfg-cmd-wrapper end';

        return $res;
    }

    public static function cfg(string|array|Collection $commands): string|true
    {
        foreach(static::prepareCfg($commands) as $cmd) {
            $process = Process::run($cmd);
            if ($process->failed()) {
                Process::run('/opt/vyatta/sbin/vyatta-cfg-cmd-wrapper end');
                return $process->errorOutput();
            }
        }

        return true;
    }

    public static function getConfig(VyOSConfig $as = VyOSConfig::NATIVE): string|array|object
    {
        $cmd = 'show configuration';
        return match ($as) {
            VyOSConfig::COMMANDS => static::op("{$cmd} commands"),
            VyOSConfig::JSON => static::op("{$cmd} json"),
            VyOSConfig::JSON_PRETTY => static::op("{$cmd} json pretty"),
            VyOSConfig::ARRAY => json_decode(static::op("{$cmd} json"), true),
            VyOSConfig::ARRAY_DOT => Arr::dot(json_decode(static::op("{$cmd} json"), true)),
            VyOSConfig::OBJECT => json_decode(static::op("{$cmd} json")),
            VyOSConfig::LITERAL => literal(json_decode(static::op("{$cmd} json"))),
            VyOSConfig::FLUENT => fluent(json_decode(static::op("{$cmd} json"))),
            default => static::op($cmd),
        };
    }
}
