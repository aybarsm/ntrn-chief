<?php

namespace App\Services;

use App\Enums\RosConfig;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;

class Ros
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

        foreach ($commands as $key => $cmd) {
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

    public static function cfg(string|array|Collection $commands, ?OutputInterface $output = null): string|true
    {
        foreach (static::prepareCfg($commands) as $cmd) {
            $process = Process::run($cmd);
            if ($output) {
                $msg = [];
                $msg[] = 'Command: <comment>'.Str::of($cmd)->chopStart('/opt/vyatta/sbin/vyatta-cfg-cmd-wrapper')->trim()->value().'</comment>';
                $msg[] = 'Status: '.($process->successful() ? '<info>Successful</info>' : '<error>Error</error>');
                $msg[] = "Exit Code: <comment>{$process->exitCode()}</comment>";
                if ($process->failed()) {
                    $msg[] = "Error Output: <error>{$process->errorOutput()}</error>";
                } else {
                    $msg[] = "Output: <info>{$process->output()}</info>";
                }
                $output->writeln(implode(' ', $msg));
            }

            if ($process->failed()) {
                Process::run('/opt/vyatta/sbin/vyatta-cfg-cmd-wrapper end');

                $rtr = 'Command ['.Str::of($cmd)->chopStart('/opt/vyatta/sbin/vyatta-cfg-cmd-wrapper')->trim()->value().'] failed.';
                $rtr .= ' Exit Code: '.$process->exitCode();
                $rtr .= ' Error Output: '.$process->errorOutput();

                return $rtr;
            }
        }

        return true;
    }

    public static function getConfig(RosConfig $as = RosConfig::NATIVE): string|array|object
    {
        $cmd = 'show configuration';

        return match ($as) {
            RosConfig::COMMANDS => static::op("{$cmd} commands"),
            RosConfig::JSON => static::op("{$cmd} json"),
            RosConfig::JSON_PRETTY => static::op("{$cmd} json pretty"),
            RosConfig::ARRAY => json_decode(static::op("{$cmd} json"), true),
            RosConfig::ARRAY_DOT => Arr::dot(json_decode(static::op("{$cmd} json"), true)),
            RosConfig::OBJECT => json_decode(static::op("{$cmd} json")),
            RosConfig::LITERAL => literal(json_decode(static::op("{$cmd} json"))),
            RosConfig::FLUENT => fluent(json_decode(static::op("{$cmd} json"))),
            default => static::op($cmd),
        };
    }
}
