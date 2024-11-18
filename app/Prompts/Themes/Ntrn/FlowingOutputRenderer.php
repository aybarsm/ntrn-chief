<?php

namespace App\Prompts\Themes\Ntrn;

use App\Prompts\FlowingOutput;
use Laravel\Prompts\Themes\Default\Concerns\DrawsBoxes;
use Laravel\Prompts\Themes\Default\Concerns\DrawsScrollbars;
use Laravel\Prompts\Themes\Default\Renderer;

class FlowingOutputRenderer extends Renderer
{
    use DrawsBoxes, DrawsScrollbars;

    public function __invoke(FlowingOutput $prompt): string
    {
        $prompt->width = $prompt->terminal()->cols() - 8;

        return $this
            ->box(
                //                $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                $this->dim($this->truncate($prompt->label, $prompt->width)),
                $this->renderText($prompt),
                //                $prompt->outputBody,
            )
            ->when(
                $prompt->hint,
                fn () => $this->hint($prompt->hint),
                fn () => $this->newLine() // Space for errors
            );
    }

    protected function renderText(FlowingOutput $prompt): string
    {
        $chunk = $prompt->outputLines
            ->take(-$prompt->rows)
            ->implode(PHP_EOL);

        return $chunk;
        //        $chunk = $prompt->outputLines
        //            ->take(($prompt->naturalFlow ? -1 : 1) * $prompt->scroll);

        //        $visible = $prompt->visible();
        //
        //        while (count($visible) < $prompt->scroll) {
        //            $visible[] = '';
        //        }
        //
        //        $longest = $this->longest($prompt->lines()) + 2;

        //        return implode(PHP_EOL, $this->scrollbar(
        //            visible: $chunk->toArray(),
        //            firstVisible: $prompt->scroll,
        //            height: $prompt->scroll,
        //            total: $prompt->outputLines->count(),
        //            width: $prompt->width + 2,
        //        ));
    }

    public function reservedLines(): int
    {
        return 5;
    }
}
