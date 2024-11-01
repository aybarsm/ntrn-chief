<?php

namespace App\Prompts;

use App\Prompts\Contracts\SpinnerContract;
use App\Traits\ConfigableOpen;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;

class Spinner extends Prompt implements SpinnerContract
{
    use Conditionable, ConfigableOpen, Macroable;

    public int $interval = 100;

    public int $count = 0;

    public bool $static = false;

    protected int $pid;

    public function __construct(public string $message = '') {}

    public function spin(\Closure $callback): mixed
    {
        $this->capturePreviousNewLines();

        if (! function_exists('pcntl_fork')) {
            return $this->renderStatically($callback);
        }

        $originalAsync = pcntl_async_signals(true);

        pcntl_signal(SIGINT, fn () => exit());

        try {
            $this->hideCursor();
            $this->render();

            $this->pid = pcntl_fork();

            if ($this->pid === 0) {
                while (true) { // @phpstan-ignore-line
                    $this->render();

                    $this->count++;

                    usleep($this->interval * 1000);
                }
            } else {
                $result = $callback();

                $this->resetTerminal($originalAsync);

                return $result;
            }
        } catch (\Throwable $e) {
            $this->resetTerminal($originalAsync);

            throw $e;
        }
    }

    protected function resetTerminal(bool $originalAsync): void
    {
        pcntl_async_signals($originalAsync);
        pcntl_signal(SIGINT, SIG_DFL);

        $this->eraseRenderedLines();
    }

    protected function renderStatically(\Closure $callback): mixed
    {
        $this->static = true;

        try {
            $this->hideCursor();
            $this->render();

            $result = $callback();
        } finally {
            $this->eraseRenderedLines();
        }

        return $result;
    }

    public function prompt(): never
    {
        throw new \RuntimeException('Spinner cannot be prompted.');
    }

    public function value(): bool
    {
        return true;
    }

    protected function eraseRenderedLines(): void
    {
        $this->clear();
        //        $lines = explode(PHP_EOL, $this->prevFrame);
        //        $this->moveCursor(-999, -count($lines) + 1);
        //        $this->eraseDown();
    }

    public function __destruct()
    {
        if (! empty($this->pid)) {
            posix_kill($this->pid, SIGHUP);
        }

        parent::__destruct();
    }

    //    public function clear(): void
    //    {
    //        $this->eraseRenderedLines();
    //    }

    //    protected function eraseRenderedLines(): void
    //    {
    //        if ($this->state === 'submit') {
    //            return;
    //        }
    //
    //        parent::eraseRenderedLines();
    //    }
    //

    //
    //    protected function resetTerminal(bool $originalAsync): void
    //    {
    //        parent::resetTerminal($originalAsync);
    //
    //        $this->state = 'submit';
    //    }
    //
    //    public function message(string $message): static
    //    {
    //        $this->message = $message;
    //
    //        return $this;
    //    }
}
