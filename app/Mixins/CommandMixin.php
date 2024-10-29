<?php

namespace App\Mixins;

use Illuminate\Support\Str;

/** @mixin \Illuminate\Console\Command */
class CommandMixin
{
    public static function forgetTask(): \Closure
    {
        return function (): void
        {
            if (static::hasMacro('task')) {
                unset(static::$macros['task']);
            }
            if (static::hasMacro('forgetTask')) {
                unset(static::$macros['forgetTask']);
            }
        };
    }
//    public static function forgetMacro(): \Closure
//    {
//        return function (string $macro): void
//        {
//            if (static::hasMacro($macro)) {
//                unset(static::$macros[$macro]);
//            }
//        };
//    }
}
