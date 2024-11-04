<?php

namespace App\Traits\Services\Helper;

use Illuminate\Process\PendingProcess;
use Illuminate\Process\ProcessResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

trait Git
{
    public static string $gitBase;
    protected static PendingProcess $gitProcess;
    protected static function gitProcess(string $command, bool $fail = false): ProcessResult
    {
        if (! isset(static::$gitBase)) {
            static::$gitBase = base_path();
        }

        if (! isset(static::$gitProcess)) {
            static::$gitProcess = Process::path(static::$gitBase);
        }

        $command = Str::of($command)->trim()->start('git ')->value();

        return static::$gitProcess->run($command)->throwIf($fail);
    }

    public static function gitRemote(string $remote = ''): Collection
    {
        $cmd = 'remote show' . (blank($remote) ? '' : " {$remote}");
        $result = static::gitProcess($cmd, true);

        return Str::of($result->output())->lines(-1, PREG_SPLIT_NO_EMPTY, true);
    }

}
