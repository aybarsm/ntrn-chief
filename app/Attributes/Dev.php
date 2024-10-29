<?php

namespace App\Attributes;

use App\Contracts\Console\TaskingCommandContract;
use Attribute;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Container\ContextualAttribute;
use Illuminate\Support\Str;

#[Attribute(Attribute::TARGET_ALL)]
class Dev implements ContextualAttribute
{
    public function __construct(
        public string $key,
        public mixed $default = null,
        protected TaskingCommandContract $command
    )
    {
    }

    public static function resolve(...$params)
    {
//        dump('Dev::resolve');
//        dump($params);
        $attribute = $params[0];
        dump($attribute);
        $container = $params[1];
        $key = Str::of($attribute->key)->start('dev.')->value();
        return $container->make('config')->get($key, $attribute->default);
    }

//    public static function after(...$params): void
//    {
//        dump('Dev::after');
//        dump($params);
////        $attribute = $params[0];
////        $container = $params[1];
////        $key = Str::of($attribute->key)->start('dev.')->value();
////        return $container->make('config')->get($key, $attribute->default);
//    }
}
