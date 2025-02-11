<?php

namespace App\Mixins;

/** @mixin \Illuminate\Support\Arr */
class ArrMixin
{
    const string BIND = \Illuminate\Support\Arr::class;

    public static function toItems(): \Closure
    {
        return function (array $array, string $prepend = ''): array {
            $items = [];

            foreach ($array as $key => $value) {
                $fullKey = $prepend ? "{$prepend}.{$key}" : $key;

                if (is_array($value) && array_values($value) !== $value) { // Check if $value is an associative array
                    $items = array_merge($items, static::toItems($value, $fullKey));
                } else {
                    $items[] = ['key' => $fullKey, 'value' => $value];
                }
            }

            return $items;
        };
    }

    public static function all(): \Closure
    {
        return function (array $array, callable $callback): bool {
            foreach ($array as $key => $value) {
                if (! $callback($value, $key)) {
                    return false;
                }
            }

            return true;
        };
    }

    public static function any(): \Closure
    {
        return function (array $array, callable $callback): bool {
            foreach ($array as $key => $value) {
                if ($callback($value, $key)) {
                    return true;
                }
            }

            return false;
        };
    }

    public static function contains(): \Closure
    {
        return function (array $array, mixed $items, bool $all = true): bool {
            $items = static::wrap($items);
            $intersection = array_intersect($array, $items);

            return $all ? count($intersection) === count($items) : count($intersection) > 0;
        };
    }

    public static function containsAll(): \Closure
    {
        return function (array $array, mixed $items): bool {
            return static::contains($array, $items);
        };
    }

    public static function containsAny(): \Closure
    {
        return function (array $array, mixed $items): bool {
            return static::contains($array, $items, false);
        };
    }
}
