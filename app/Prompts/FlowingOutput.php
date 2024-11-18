<?php

namespace App\Prompts;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FlowingOutput extends Prompt
{
    public int $width = 120;

    public string $outputBody = '';

    public Collection $outputLines;

    protected bool $originalAsync;

    public function __construct(
        public string $label = '',
        public int $rows = 5,
        public string $hint = '',
        public bool $naturalFlow = true,
    ) {
        $this->outputLines = Str::lines(str_repeat(PHP_EOL, $this->rows), -1);
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

    public function addOutput(string $output): void
    {
        $output = Str::lines(trim($output), -1, PREG_SPLIT_NO_EMPTY, true);
        if ($output->isEmpty()) {
            return;
        }
        $this->outputLines = $this->outputLines->concat($output);
        if ($this->state === 'active') {
            $this->start();
        } else {
            $this->render();
        }
    }

    public function prompt(): never
    {
        throw new \RuntimeException('Process Output cannot be prompted.');
    }

    public function value(): string
    {
        return $this->outputBody;
    }
}
