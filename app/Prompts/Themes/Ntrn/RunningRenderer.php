<?php

namespace App\Prompts\Themes\Ntrn;

use App\Prompts\Running;
use Laravel\Prompts\Themes\Default\Renderer;

class RunningRenderer extends Renderer
{
    public function __invoke(Running $running): string
    {
        return $this->line('Running...');
    }
}
