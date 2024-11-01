<?php

namespace App\Prompts\Themes\Ntrn;

use App\Prompts\Spinner;
use Laravel\Prompts\Themes\Default\Renderer as LaravelRenderer;

class SpinnerRenderer extends LaravelRenderer
{
    //    protected array $frames = ['⠂', '⠒', '⠐', '⠰', '⠠', '⠤', '⠄', '⠆'];
    protected array $frames = ['⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇'];

    protected string $staticFrame = '⠶';

    protected int $interval = 75;

    protected function defaultSpinner(Spinner $spinner): string
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
