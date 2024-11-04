<?php

namespace App\Prompts\Themes\Ntrn;

class ClearRenderer extends Renderer
{
    /**
     * Clear the terminal.
     */
    public function __invoke(): string
    {
        return "\033[H\033[J";
    }
}