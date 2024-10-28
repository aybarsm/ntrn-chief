<?php

namespace App\Prompts;

use App\Traits\Prompt\Statable;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;

class Progress extends \Laravel\Prompts\Progress
{
    use Macroable, Conditionable, Statable;

    public bool $autoFinish = true;
    public bool $showPercentage = true;

    public ?string $numberType = null;
    public array $numberOptions = [];

    public static array $defaultStates = [
        'initial' => ['color' => 'blue', 'labelApply' => 'dim'],
        'active' => ['color' => 'yellow', 'labelApply' => 'cyan'],
        'submit' => ['color' => 'green', 'labelApply' => 'dim'],
        'error' => ['color' => 'red', 'messageType' => 'error'],
        'cancel' => ['color' => 'red', 'message' => 'Cancelled.', 'messageType' => 'warning'],
        'default' => ['color' => 'yellow', 'labelApply' => 'cyan'],
    ];

    public function __construct(public string $label = '', public iterable|int $steps = 0, public string $hint = '')
    {
        $tempSteps = is_int($steps) && $steps == 0 ? -1 : $steps;

        parent::__construct($label, $tempSteps, $hint);

        if (is_int($steps) && $tempSteps != $steps) {
            $this->total = $steps;
        }

        foreach(['label' => $label, 'hint' => $hint] as $stateEntry => $entryValue){
            if (! blank($entryValue)){
                static::$defaultStates['default'][$stateEntry] = $entryValue;
            }
        }

        $this->setStates(static::$defaultStates);
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
        $this->progress += $step;

        if ($this->progress > $this->total) {
            $this->progress = $this->total;
        }

        if ($this->autoFinish && $this->progress === $this->total){
            $this->finish();
        }else {
            $this->render();
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
