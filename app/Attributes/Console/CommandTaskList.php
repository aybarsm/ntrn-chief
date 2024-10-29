<?php

namespace App\Attributes\Console;

use Attribute;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Container\ContextualAttribute;
use App\Contracts\Console\TaskingCommandContract;
#[Attribute(Attribute::TARGET_PARAMETER)]
class CommandTaskList implements ContextualAttribute
{
    public function __construct(
        public ?TaskingCommandContract $command = null
    )
    {

    }

    public static function resolve(self $attribute, Container $container)
    {
        return [];
    }
}
