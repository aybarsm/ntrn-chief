<?php

namespace App\Prompts;

use App\Prompts\Contracts\ProgressContract;
use App\Traits\ConfigableOpen;
use Illuminate\Support\Str;
use Symfony\Component\Console\Cursor;

class Progress extends Prompt implements ProgressContract
{
    use ConfigableOpen;

    public int $progress = 0;

    public int $total = 0;

    protected bool $originalAsync;

    public static array $defaults = [
        'state' => [
            'initial' => ['color' => 'blue', 'label' => ['method' => 'dim']],
            'active' => ['color' => 'yellow', 'label' => ['method' => 'cyan']],
            'submit' => ['color' => 'green', 'label' => ['method' => 'dim']],
            'error' => ['color' => 'red', 'message' => ['method' => 'error']],
            'cancel' => ['color' => 'red', 'message' => ['value' => 'Cancelled.', 'method' => 'warning']],
            'default' => ['color' => 'yellow', 'label' => ['method' => 'cyan']],
        ],
        'auto' => ['clear' => false, 'start' => true, 'finish' => true],
        'number' => ['type' => null, 'options' => []],
        'show' => ['percentage' => true],
    ];

    public function __construct(public iterable|int $steps = 0, string $label = '', string $hint = '')
    {
        $this->total = match (true) { // @phpstan-ignore assign.propertyType
            is_int($this->steps) => $this->steps,
            is_countable($this->steps) => count($this->steps),
            is_iterable($this->steps) => iterator_count($this->steps),
            default => throw new \InvalidArgumentException('Unable to count steps.'),
        };

        $config = static::$defaults;

        foreach (['label' => $label, 'hint' => $hint] as $entryType => $entryValue) {
            if (! blank($entryValue)) {
                $config['state']['default'][$entryType]['value'] = $entryValue;
            }
        }

        $this->configables = $config;
    }

    public function value(): bool
    {
        return true;
    }

    public function map(\Closure $callback): array
    {
        $this->start();

        $result = [];

        try {
            if (is_int($this->steps)) {
                for ($i = 0; $i < $this->steps; $i++) {
                    $result[] = $callback($i, $this);
                    $this->advance();
                }
            } else {
                foreach ($this->steps as $step) {
                    $result[] = $callback($step, $this);
                    $this->advance();
                }
            }
        } catch (\Throwable $e) {
            $this->state = 'error';
            $this->render();
            $this->restoreCursor();
            $this->resetSignals();

            throw $e;
        }

        if (! blank($this->config('get', 'hint.value'))) {
            // Just pause for one moment to show the final hint
            // so it doesn't look like it was skipped
            usleep(250_000);
        }

        $this->finish();

        return $result;
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

    public function advance(int $step = 1): void
    {
        if ($this->state == 'initial' && $this->progress == 0 && $step > 0 && $this->config('get', 'auto.start') === true) {
            $this->start();
        }

        $this->progress += $step;

        if ($this->progress > $this->total) {
            $this->progress = $this->total;
        }

        if ($this->progress === $this->total && $this->config('get', 'auto.finish') === true) {
            $this->finish();
        } else {
            $this->render();
        }

        if ($this->progress === $this->total && $this->config('get', 'auto.clear') === true) {
            $this->clear();
        }
    }

    public function finish(): void
    {
        $this->state = 'submit';
        $this->render();
        $this->restoreCursor();
        $this->resetSignals();
        $showFinish = $this->config('get', 'show.finish', 0);
        if (is_int($showFinish) && $showFinish > 0) {
            sleep($showFinish);
        }
    }

    public function render(): void
    {
        parent::render();
    }

    public function label(string $label, string $state = 'default', string $path = 'value'): static
    {
        return $this->state("{$state}.label.{$path}", $label);
    }

    public function hint(string $hint, string $state = 'default', string $path = 'value'): static
    {
        return $this->state("{$state}.hint.{$path}", $hint);
    }

    public function percentage(): int|float
    {
        return $this->total === 0 ? 0 : $this->progress / $this->total;
    }

    public function prompt(): never
    {
        throw new \RuntimeException('Progress Bar cannot be prompted.');
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

    protected function setConf(string $prefix, string $path, mixed $value): static
    {
        $path = Str::of($path)->trim()->trim('.')->start("{$prefix}.")
            ->when(
                fn ($str) => $str->value() == "{$prefix}.",
                fn ($str) => $str->rtrim('.')
            )->value();

        $this->config('set', $path, $value);

        return $this;
    }

    public function state(string $path, mixed $value): static
    {
        $this->setConf(__FUNCTION__, $path, $value);

        if ($this->state !== 'initial') {
            $this->render();
        }

        return $this;
    }

    public function auto(string $path, mixed $value): static
    {
        return $this->setConf(__FUNCTION__, $path, $value);
    }

    public function number(string $path, mixed $value): static
    {
        return $this->setConf(__FUNCTION__, $path, $value);
    }

    public function show(string $path, mixed $value): static
    {
        return $this->setConf(__FUNCTION__, $path, $value);
    }

    public function total(int $total): static
    {
        if (! is_int($this->steps)) {
            return $this;
        }

        if ($total > 0 && $this->total != $total) {
            $this->total = $total;
        }

        if ($this->state == 'active') {
            $this->render();
        }

        return $this;
    }

    public function progress(int $progress): void
    {
        if ($progress > 0 && $this->total > 0 && $progress > $this->progress) {
            $this->advance($progress - $this->progress);
        }
    }

    //    public function clear(): void
    //    {
    //        $this->eraseRenderedLines();
    // //        $this->cursor->restorePosition();
    // //        $this->cursor->clearOutput();
    // //        $this->cursor = null;
    //    }
    //    protected function eraseRenderedLines(): void
    //    {
    //        $lines = explode(PHP_EOL, $this->prevFrame);
    //        info("Erasing " . count($lines) . " lines y: ". (-count($lines) + 1));
    //        info("Frames: " . $this->prevFrame);
    //        info('######');
    //        $this->moveCursor(-999, -count($lines) + 1);
    //        $this->eraseDown();
    //        $this->prevFrame = '';
    //    }
}
