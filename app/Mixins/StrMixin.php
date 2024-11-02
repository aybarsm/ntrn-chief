<?php

namespace App\Mixins;

/** @mixin \Illuminate\Support\Str */
class StrMixin
{
    public static function removeEmptyLines(): \Closure
    {
        return function (string $str): string {
            $init = static::replaceMatches('/^\s*[\r\n]+|[\r\n]+\s*\z/', '', $str);

            return static::replaceMatches('/(\n\s*){2,}/', "\n", $init);
        };
    }
}
