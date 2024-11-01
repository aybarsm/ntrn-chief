<?php

namespace App\Mixins;

/** @mixin \Illuminate\Support\Str */
class StrMixin
{
    public static function removeEmptyLines(): \Closure
    {
        return function (string $str): string
        {
            return static::of($str)->removeEmptyLines()->value();
        };
    }
}
