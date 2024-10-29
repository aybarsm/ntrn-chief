<?php

namespace App\Prompts;

use App\Traits\ConfigableOpen;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Laravel\Prompts\Spinner as LaravelSpinner;
class Spinner extends LaravelSpinner
{
    use Macroable, Conditionable, ConfigableOpen;

    protected function eraseRenderedLines(): void
    {
        if ($this->state === 'submit') {
            return;
        }

        parent::eraseRenderedLines();
    }

    public function clear(): void
    {
        $this->eraseRenderedLines();
    }

    protected function resetTerminal(bool $originalAsync): void
    {
        parent::resetTerminal($originalAsync);

        $this->state = 'submit';
    }

    public function message(string $message): static
    {
        $this->message = $message;

        return $this;
    }
}
