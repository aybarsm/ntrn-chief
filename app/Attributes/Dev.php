<?php

namespace App\Attributes;

use Attribute;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Container\ContextualAttribute;
use Illuminate\Support\Str;

#[Attribute(Attribute::TARGET_ALL)]
class Dev implements ContextualAttribute
{
    public function __construct(public string $key, public mixed $default = null)
    {
    }

    public static function resolve(self $attribute, Container $container)
    {
        dump('Dev::resolve');
        $key = Str::of($attribute->key)->start('dev.')->value();
        return $container->make('config')->get($key, $attribute->default);
    }
}
