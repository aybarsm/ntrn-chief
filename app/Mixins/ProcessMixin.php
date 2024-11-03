<?php

namespace App\Mixins;

/** @mixin \Illuminate\Process\Factory */
class ProcessMixin
{
    public static function singleLine(string $src = 'output'): \Closure
    {
        return function (string $src = 'output'): string
        {
            return match($src) {
                'error' => $this->errorOutput(),
            };
        };
    }
}
