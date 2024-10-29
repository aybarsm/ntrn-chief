<?php

namespace App\Attributes\Console;

use App\Enums\IndicatorType;
use Attribute;
use Illuminate\Contracts\Container\ContextualAttribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class TaskingCommand implements ContextualAttribute
{
    public function __construct(
        public string $method,
        public ?IndicatorType $indicatorType = null,
    )
    {
    }

    public static function resolve(self $attribute, \Illuminate\Contracts\Container\Container $container): array
    {
        dump('TaskingCommand::resolve');
        return [
            'method' => $attribute->method,
            'indicatorType' => $attribute->indicatorType,
        ];
    }
}
