<?php

namespace App\Services;

use App\Enums\VyOSConfig;
use Illuminate\Support\Arr;
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

    protected static function prepCfg(array $commands): array
    {
        $res = [];

        Arr::map($commands, function ($cmd, $key) use ($commands, &$res) {
            $cmd = Str::of($cmd)->trim()
                ->chopStart('/opt/vyatta/sbin/vyatta-cfg-cmd-wrapper')
                ->trim()
                ->value();

            if ($key === array_key_first($commands) and $cmd !== 'begin') {
                $res[] = '/opt/vyatta/sbin/vyatta-cfg-cmd-wrapper begin';
            }

            $res[] = "/opt/vyatta/sbin/vyatta-cfg-cmd-wrapper {$cmd}";

            if ($key === array_key_last($commands) and $cmd !== 'end') {
                $res[] = '/opt/vyatta/sbin/vyatta-cfg-cmd-wrapper end';
            }
        });

        return $res;
    }

    public static function cfg(string|array $commands): string|true
    {
        $commands = static::prepCfg(Arr::wrap($commands));
        foreach($commands as $cmd) {
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
            VyOSConfig::OBJECT => json_decode(static::op("{$cmd} json")),
            VyOSConfig::LITERAL => literal(json_decode(static::op("{$cmd} json"))),
            VyOSConfig::FLUENT => fluent(json_decode(static::op("{$cmd} json"))),
            default => static::op($cmd),
        };
    }
}
