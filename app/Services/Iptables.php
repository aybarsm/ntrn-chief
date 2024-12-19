<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class Iptables
{
    public static function ruleExists(string $rule): bool
    {
        $rule = Str::trim($rule);
        $pattern = config('os.iptables.pattern');
        throw_if(! Str::isMatch($pattern, $rule), "Rule [{$rule}] does not match pattern [{$pattern}]");

        $replace = config('os.iptables.check.replace');

        return Process::run(Str::replaceMatches($pattern, $replace, $rule))->successful();
    }
}
