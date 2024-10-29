<?php

namespace App\Attributes\Console;

use App\Enums\IndicatorType;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class CommandTask
{
    public function __construct(
        public string $method,
        public ?IndicatorType $indicatorType = null,
        public string $title = '',
    )
    {
    }
}
