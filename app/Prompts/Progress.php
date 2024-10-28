<?php

namespace App\Prompts;

use App\Traits\Configable;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;

class Progress extends \Laravel\Prompts\Progress
{
    use Macroable, Conditionable, Configable;
    public static array $defaultConfig = [
        'state' => [
            'initial' => ['color' => 'blue', 'label' => ['method' => 'dim']],
            'active' => ['color' => 'yellow', 'label' => ['method' => 'cyan']],
            'submit' => ['color' => 'green', 'label' => ['method' => 'dim']],
            'error' => ['color' => 'red', 'message' => ['method' => 'error']],
            'cancel' => ['color' => 'red', 'message' => ['value' => 'Cancelled.', 'method' => 'warning']],
            'default' => ['color' => 'yellow', 'label' => ['method' => 'cyan']],
        ],
        'auto' => [
            'clear' => false,
            'start' => true,
            'finish' => true,
        ],
        'number' => [
            'type' => null,
            'options' => [],
        ],
        'show' => [
            'percentage' => true,
        ],
    ];

    public function __construct(public string $label = '', public iterable|int $steps = 0, public string $hint = '')
    {
        $tempSteps = is_int($steps) && $steps == 0 ? -1 : $steps;

        parent::__construct($label, $tempSteps, $hint);

        if (is_int($steps) && $tempSteps != $steps) {
            $this->total = $steps;
        }

        foreach(['label' => $label, 'hint' => $hint] as $entryType => $entryValue){
            if (! blank($entryValue)){
                data_set(static::$defaultConfig, "state.default.{$entryType}.value", $entryValue);
            }
        }

        $this->configables = static::$defaultConfig;
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

    public function conf(string|array $path, mixed $default = null): mixed
    {
        return $this->config('get', $path, $default);
    }

    public function state(string $path, mixed $value): static
    {
        $this->setConf(__FUNCTION__, $path, $value);

        return $this;
    }

    public function auto(string $path, mixed $value): static
    {
        $this->setConf(__FUNCTION__, $path, $value);

        return $this;
    }

    public function number(string $path, mixed $value): static
    {
        $this->setConf(__FUNCTION__, $path, $value);

        return $this;
    }

    public function show(string $path, mixed $value): static
    {
        $this->setConf(__FUNCTION__, $path, $value);

        return $this;
    }

    public function total(int $total): static
    {
        if (! is_int($this->steps)){
            return $this;
        }

        if ($total > 0 && $this->total != $total) {
            $this->total = $total;
        }

        if ($this->state == 'active'){
            $this->render();
        }

        return $this;
    }

    public function label(string $label): static
    {
        parent::label($label);

        if ($this->state !== 'initial'){
            $this->render();
        }

        return $this;
    }

    public function hint(string $hint): static
    {
        parent::hint($hint);

        if ($this->state !== 'initial'){
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

    public function advance(int $step = 1): void
    {
        if ($this->conf('auto.start') === true && $this->state == 'initial' && $this->progress == 0 && $step > 0){
            $this->start();
        }

        $this->progress += $step;

        if ($this->progress > $this->total) {
            $this->progress = $this->total;
        }

        if ($this->conf('auto.finish') === true && $this->progress === $this->total){
            $this->finish();
        }else {
            $this->render();
        }

        if ($this->conf('auto.clear') === true && $this->progress === $this->total){
            $this->clear();
        }
    }

    public function percentage(): int|float
    {
        return $this->total === 0 ? 0 : $this->progress / $this->total;
    }

    public function clear(): void
    {
        $this->eraseRenderedLines();
    }
    protected function eraseRenderedLines(): void
    {
        $lines = explode(PHP_EOL, $this->prevFrame);
        $this->moveCursor(-999, -count($lines) + 1);
        $this->eraseDown();
    }
}
