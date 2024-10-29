<?php

namespace App\Attributes\Console;

use Attribute;
use App\Enums\IndicatorType;

#[Attribute(Attribute::TARGET_METHOD)]
class CommandTask
{
    public function __construct(
        public int $order,
        public string $title,
        public ?IndicatorType $indicatorType = null,
        public ?string $method = null,
    )
    {

    }
}
