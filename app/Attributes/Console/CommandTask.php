<?php

namespace App\Attributes\Console;

use App\Enums\IndicatorType;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class CommandTask
{
    public int $id;

    public int $position;

    public array $messages = [];

    public string $messageTitle;

    public function __construct(
        public string $method,
        public ?IndicatorType $indicatorType = null,
        public string $title = '',
        public bool $explicit = false,
        public bool $skipRest = false,
    ) {}

    public function addMessage(string $message): void
    {
        $this->messages[] = $message;
    }
}
