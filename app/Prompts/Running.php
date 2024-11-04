<?php

namespace App\Prompts;

use App\Prompts\Contracts\RunningContract;
use App\Traits\ConfigableOpen;

class Running extends Prompt implements RunningContract
{
    use ConfigableOpen;

    protected bool $originalAsync;

    public int $steps = 20;

    public int $pos = 0;

    public bool $forward = true;

    public function __construct(public string $label = '') {}

    public function move(): void
    {
        $this->render();
    }

    public function start(): void
    {
        $this->capturePreviousNewLines();

        if (function_exists('pcntl_signal')) {
            $this->originalAsync = pcntl_async_signals(true);
            pcntl_signal(SIGINT, function () {
                $this->state = 'cancel';
                $this->render();
                exit();
            });
        }

        $this->state = 'active';
        $this->hideCursor();
        $this->render();
    }

    public function finish(): void
    {
        $this->state = 'submit';
        $this->render();
        $this->restoreCursor();
        $this->resetSignals();

    }

    protected function resetSignals(): void
    {
        if (isset($this->originalAsync)) {
            pcntl_async_signals($this->originalAsync);
            pcntl_signal(SIGINT, SIG_DFL);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function value(): mixed
    {
        return true;
    }
}
