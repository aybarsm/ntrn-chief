<?php

namespace App\Prompts\Themes\Ntrn;

use App\Prompts\PausePrompt;
use Laravel\Prompts\Themes\Default\Concerns\DrawsBoxes;
class PausePromptRenderer extends Renderer
{
    use DrawsBoxes;

    /**
     * Render the pause prompt.
     */
    public function __invoke(PausePrompt $prompt): string
    {
        $lines = explode(PHP_EOL, $prompt->message);

        $color = $prompt->state === 'submit' ? 'green' : 'gray';

        foreach ($lines as $line) {
            $this->line(" {$this->{$color}($line)}");
        }

        return $this;
    }
}
