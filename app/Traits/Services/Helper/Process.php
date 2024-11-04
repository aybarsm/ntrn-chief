<?php

namespace App\Traits\Services\Helper;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

trait Process
{
    public static function buildProcessArgs(array $args = [], array $defaults = []): array
    {
        $built = [];

        foreach ([$defaults, $args] as $level) {
            foreach($level as $argKey => $argVal) {
                $arg = Str::of(match(true){
                    is_int($argKey) && blank($argVal) => (string) $argKey,
                    is_int($argKey) && ! blank($argVal) => (string) $argVal,
                    default => "{$argKey}={$argVal}",
                })->trim();

                $argKey = $arg->ltrim('-')->when(
                    fn (Stringable $str) => $str->contains('='),
                    fn (Stringable $str) => $str->before('='),
                )->prepend('--')->value();

                if (in_array($argKey, array_keys($built))) {
                    continue;
                }

                $argVal = $arg->when(
                    fn (Stringable $str) => $str->contains('='),
                    fn (Stringable $str) => $str->after('=')->ltrim('='),
                    fn (Stringable $str) => $str->makeEmpty(),
                )->value();

                $built[$argKey] = $argVal;
            }
        }

        return array_values(Arr::map($built, fn ($value, $key) => blank($value) ? $key : "{$key}={$value}"));
    }

}
