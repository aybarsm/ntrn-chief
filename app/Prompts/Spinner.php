<?php

namespace App\Prompts;

use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;

class Spinner extends \Laravel\Prompts\Spinner
{
    use Macroable, Conditionable;

    public function spin(\Closure $callback): mixed
    {
        $this->state = 'active';

        $result = parent::spin($callback);

        $this->state = 'submit';

        return $result;
    }

    protected function eraseRenderedLines(): void
    {
        $lines = explode(PHP_EOL, $this->prevFrame);
        $this->moveCursor(-999, -count($lines) + 1);
        $this->eraseDown();
    }

    protected function resetTerminal(bool $originalAsync): void
    {
        pcntl_async_signals($originalAsync);
        pcntl_signal(SIGINT, SIG_DFL);

        $this->eraseRenderedLines();
    }

    public function message(string $message): static
    {
        $this->message = $message;

        return $this;
    }
}
