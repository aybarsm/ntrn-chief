<?php

namespace App\Attributes\Console;

use App\Enums\IndicatorType;
use Attribute;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Container\ContextualAttribute;

#[Attribute(Attribute::TARGET_METHOD)]
class CommandTask implements ContextualAttribute
{
    public function __construct(public int $order, public string $title, public ?IndicatorType $indicator = null)
    {

    }

    public static function resolve(self $attribute, Container $container)
    {
        dump('Commandtask::resolve');
        return [];
    }
}
