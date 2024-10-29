<?php

namespace App\Prompts;

use App\Prompts\Spinner;
use Laravel\Prompts\Themes\Default\SpinnerRenderer as LaravelSpinnerRenderer;
class SpinnerRenderer extends LaravelSpinnerRenderer
{
    protected array $frames = ['⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇'];

    protected string $staticFrame = '⠶';

    protected int $interval = 75;

    protected function defaultSpinner(Spinner|\Laravel\Prompts\Spinner $spinner): string
    {
        if ($spinner->static) {
            return $this->line(" {$this->cyan($this->staticFrame)} {$spinner->message}");
        }

        $spinner->interval = $this->interval;

        $frame = $this->frames[$spinner->count % count($this->frames)];

        return $this->line(" {$this->cyan($frame)} {$spinner->message}");
    }

    public function __invoke(Spinner|\Laravel\Prompts\Spinner $spinner): string
    {
        return match ($spinner->state) {
            default => $this->defaultSpinner($spinner),
        };
    }
}
