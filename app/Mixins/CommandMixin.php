<?php

namespace App\Mixins;

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
}
