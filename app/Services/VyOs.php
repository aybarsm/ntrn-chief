<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class VyOs
{
    public static function getConfig($asArray = true): array|string
    {
        $cmd = 'show configuration'.($asArray ? ' json' : '');
        $process = Process::run($cmd);
        throw_if($process->failed(), 'Failed to retrieve VyOS configuration');
        $output = $process->output();
        throw_if($asArray and ! Str::isJson($output), 'Invalid JSON output from VyOS');

        return $asArray ? json_decode($output, true) : $output;
    }
}
