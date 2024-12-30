<?php

namespace App\Attributes;
use Attribute;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Container\ContextualAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class AppBinding implements ContextualAttribute
{
    public function __construct(public string $key, public array $parameters = [])
    {
    }

    public static function resolve(self $attribute, Container $container): mixed
    {
        return app($attribute->key, $attribute->parameters);
    }
}
