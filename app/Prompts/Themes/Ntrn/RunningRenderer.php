<?php

namespace App\Prompts\Themes\Ntrn;

use App\Prompts\Running;
use Laravel\Prompts\Themes\Default\Concerns\DrawsBoxes;
use Laravel\Prompts\Themes\Default\Renderer;

class RunningRenderer extends Renderer
{
    use DrawsBoxes;
    protected string $char = '███ ███';
    public function __invoke(Running $running): string
    {
        if ($running->pos >= 5) {
            $running->pos = 0;
        }

        $line = $running->label . str_repeat('.', $running->pos);
        $running->pos = $running->pos + 1;
        return $this->line($this->cyan($line));

//        $steps = min($this->minWidth, $running->terminal()->cols()) - mb_strlen($this->char);
//        if ($running->pos >= $steps) {
//            $running->pos = 0;
//            $running->forward = ! $running->forward;
//        }
//
//        if ($running->forward) {
//            $left = str_repeat(' ', $running->pos);
//        }else {
//            $left = str_repeat(' ', $steps - $running->pos);
//        }
//
//        $running->pos = $running->pos + 1;

//        return $this->box(
//            title: $running->label,
//            body: "{$left}{$this->char}",
//            color: 'cyan'
//        );

//        return $this->line("{$left}{$this->char}");

    }
}
