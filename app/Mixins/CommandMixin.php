<?php

namespace App\Mixins;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/** @mixin \Illuminate\Console\Command */
class CommandMixin
{
//    public static function prompt(): \Closure
//    {
//        return function (string $name, string $theme = 'ntrn', ...$params): mixed
//        {
//            $prompts = \App\Prompts\Prompt::getTheme($theme);
//            throw_if(blank($prompts), "Prompt theme [{$theme}] not found.");
//
//            $key = Str::of($name)->trim()->lower()->chopEnd('prompt')->value();
//
//            $prompts = Arr::mapWithKeys(array_keys($prompts),
//                fn ($class) => [Str::of($class)->afterLast('\\')->lower()->chopEnd('prompt')->value() => $class]
//            );
//            throw_if(! in_array($key, array_keys($prompts)), "Prompt [{$name}] not found in theme [{$theme}].");
//
//            return new $prompts[$key](...$params);
//        };
//    }

    public static function testMe(): \Closure
    {
        return function (): string
        {
            return 'asdasdasdass';
        };
    }
}
