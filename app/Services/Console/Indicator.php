<?php

namespace App\Services\Console;

use App\Contracts\Services\Console\IndicatorContract;
use App\Enums\IndicatorType;
use App\Prompts\Progress;
use App\Prompts\Spinner;

class Indicator implements IndicatorContract
{
    protected Progress|Spinner|null $indicator = null;
    protected array $indicatorOptions = [];

    public function __construct(
        protected IndicatorType $type,
        ...$options
    )
    {
        $this->indicatorOptions = $options;

        $this->indicator = match ($type) {
            IndicatorType::SPINNER => new Spinner(...$options),
            IndicatorType::PROGRESS => new Progress(...$options),
            default => throw new \InvalidArgumentException("Invalid indicator type [{$type->value}]"),
        };
    }

    public function show(): static
    {
        if ($this->getIndicator()->state == 'initial') {
            $this->getIndicator()->render();
        }

        return $this;
    }

    protected function requireIndicatorType(IndicatorType $type, string $method): void
    {
        throw_if($this->type != $type, new \LogicException("Method [{$method}] is available for [{$type->value}]"));
    }

    public function isReady(): bool
    {
        return $this->indicator !== null;
    }

    public function getIndicator(): Progress|Spinner|null
    {
        return $this->indicator;
    }

    public function start(): void
    {
        $this->requireIndicatorType(IndicatorType::PROGRESS, __FUNCTION__);

        $this->getIndicator()->start();
    }

    public function label(string $label): static
    {
        $this->requireIndicatorType(IndicatorType::PROGRESS, __FUNCTION__);

        return $this;
    }

    public function hint(string $hint): static
    {
        $this->requireIndicatorType(IndicatorType::PROGRESS, __FUNCTION__);

        return $this;
    }

    public function progress(int $step = 0, int $total = 0, int $progress = 0): static
    {
        $this->requireIndicatorType(IndicatorType::PROGRESS, __FUNCTION__);

        $this->getIndicator()->total($total);

        if ($progress > 0) {
            $this->getIndicator()->progress($progress);
        }elseif ($step > 0) {
            $this->getIndicator()->advance($step);
        }

        return $this;
    }

    public function spin(\Closure $callback): mixed
    {
        $this->requireIndicatorType(IndicatorType::SPINNER, __FUNCTION__);

        return $this->getIndicator()->spin($callback);
    }

}
