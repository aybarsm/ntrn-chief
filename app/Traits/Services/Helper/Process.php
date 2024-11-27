<?php

namespace App\Traits\Services\Helper;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

trait Process
{
    public static function buildProcessCmd(string|array $cmd, array $args = [], array $defaults = []): string
    {
        if (is_array($cmd)) {
            $cmd = Arr::join($cmd, ' ');
        }

        return trim(trim($cmd).' '.static::buildProcessArgs($args, $defaults, true));
    }

    protected static function resolveProcessArg(string|int $argKey, mixed $argVal, bool $assoc = false): array
    {
        $arg = Str::of(match (true) {
            is_int($argKey) && blank($argVal) => (string) $argKey,
            is_int($argKey) && ! blank($argVal) => (string) $argVal,
            default => "{$argKey}={$argVal}",
        })->trim();

        $argKey = $arg->ltrim('-')->when(
            fn (Stringable $str) => $str->contains('='),
            fn (Stringable $str) => $str->before('='),
        )->prepend('--')->value();

        $argVal = $arg->when(
            fn (Stringable $str) => $str->contains('='),
            fn (Stringable $str) => $str->after('=')->ltrim('='),
            fn (Stringable $str) => $str->makeEmpty(),
        )->value();

        return $assoc ? [$argKey => $argVal] : [$argKey, $argVal];
    }

    public static function buildProcessArgs(array $args = [], array $defaults = [], bool $asString = false, bool $allowMultiple = true, bool $exclusiveDefaults = true): string|array
    {
        $built = collect();

        foreach ([$defaults, $args] as $stage => $level) {
            $exclusive = $allowMultiple && $exclusiveDefaults && $stage === 1;
            foreach ($level as $key => $val) {
                [$key, $val] = static::resolveProcessArg($key, $val);

                if ((! $allowMultiple || $exclusive) && $built->contains(fn ($item) => $item['key'] === $key && (! $exclusive || $item['stage'] === 0))) {
                    continue;
                }

                $built->push([
                    'key' => $key,
                    'val' => $val,
                    'final' => $key . (blank($val) ? '' : "={$val}"),
                    'stage' => $stage,
                ]);
            }
        }

        $built = $built->pluck('final')->toArray();

        return $asString ? Arr::join($built, ' ') : $built;
    }
}
