<?php

namespace App\Prompts;

class SpinnerRenderer extends \Laravel\Prompts\Themes\Default\SpinnerRenderer
{
    protected array $frames = ['⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇'];
}
