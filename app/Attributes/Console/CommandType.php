<?php

namespace App\Attributes\Console;

use Attribute;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Container\ContextualAttribute;
#[Attribute(Attribute::TARGET_CLASS)]
class CommandType implements ContextualAttribute
{
    public function __construct(public ?string $type)
    {

    }

    public static function resolve(self $attribute, Container $container)
    {
        return $attribute->type;
    }
}
