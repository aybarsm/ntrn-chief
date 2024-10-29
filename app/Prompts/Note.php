<?php

namespace App\Prompts;

use App\Traits\ConfigableOpen;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Laravel\Prompts\Note as LaravelNote;

class Note extends LaravelNote
{
    use Macroable, Conditionable, ConfigableOpen;

    protected function eraseRenderedLines(): void
    {
        $lines = explode(PHP_EOL, $this->prevFrame);
        $this->moveCursor(-999, -count($lines) + 1);
        $this->eraseDown();
    }

    public function value(): bool
    {
        return true;
    }

    protected function resetSignals(): void
    {
        if (isset($this->originalAsync)) {
            pcntl_async_signals($this->originalAsync);
            pcntl_signal(SIGINT, SIG_DFL);
        }
    }

    public function __destruct()
    {
        $this->restoreCursor();
    }

    public function render(): void
    {
        parent::render();
    }

    public function finish(): void
    {
        $this->state = 'submit';
        $this->render();
        $this->restoreCursor();
        $this->resetSignals();
    }

    public function display(): void
    {
        $this->render();
    }

    public function prompt(): bool
    {
        $this->render();
    }

    public function clear(): void
    {
        $this->eraseRenderedLines();
    }

}
