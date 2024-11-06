<?php

namespace App\Attributes;

use App\Contracts\Console\TaskingCommandContract;
use Attribute;
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class TaskMethod
{
    public const string BIND = 'method';

    public int $position;

    public function __construct(
        public string $method,
        public string $title = '',
        public bool $bail = false,
        public array $whenFailedSkip = [],
    ) {}

    public function afterResolve(int $position, $attribute, $object): void
    {
        $this->position = $position;
    }
}
