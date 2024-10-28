<?php

namespace App\Prompts;

use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;

class Spinner extends \Laravel\Prompts\Spinner
{
    use Macroable, Conditionable;
    public int $interval = 3;

    public function spin(\Closure $callback): mixed
    {
        $this->state = 'active';

        return parent::spin($callback);
    }

    public function message(string $message): static
    {
        $this->message = $message;

        return $this;
    }
}
