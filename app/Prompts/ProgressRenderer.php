<?php

namespace App\Prompts;

use Illuminate\Support\Number;

class ProgressRenderer extends \Laravel\Prompts\Themes\Default\ProgressRenderer
{
    protected string $barCharacter = '█';
    public function __invoke(\App\Prompts\Progress|\Laravel\Prompts\Progress $progress): string
    {
        $completed = $progress->progress;
        $total = $progress->total;

        if ($progress->numberType !== null && method_exists(Number::class, $progress->numberType)){
            $completed = Number::{$progress->numberType}($completed, ...$progress->numberOptions);
            $total = Number::{$progress->numberType}($total, ...$progress->numberOptions);
        }

        $hint = $progress->getState("{$progress->state}.hint", $progress->hint);
        $info = $progress->getState("{$progress->state}.info", "{$completed} / {$total}");
        $color = $progress->getState("{$progress->state}.color", 'yellow');

        $message = $progress->getState("{$progress->state}.message", null, false);
        $messageType = $progress->getState("{$progress->state}.messageType", null, false);
        $messageEligible = $message !== null && $messageType !== null && method_exists($this, $messageType);

        $label = $progress->getState("{$progress->state}.label", $progress->label);
//        $label = $progress->showPercentage ? $label.' (' . number_format($progress->percentage() * 100, 2) . '%)' : $label;
        $label = $progress->showPercentage ? $label . ' ' . round($progress->percentage() * 100) . '%' : $label;

        $label = $this->truncate($label, $progress->terminal()->cols() - 6);
        $labelApply = $progress->getState("{$progress->state}.labelApply", null, false);

        if ($labelApply !== null && method_exists($this, $labelApply)){
            $label = $this->{$labelApply}($label);
        }

        $filled = str_repeat($this->barCharacter, (int) ceil($progress->percentage() * min($this->minWidth, $progress->terminal()->cols() - 6)));
        $filled = $this->dim($filled);

        return $this->box(
            title: $label,
            body: $filled,
            color: $color,
            info: $info,
        )->when(
            ! blank($hint),
            fn () => $this->hint($hint),
            fn () => $this->newLine() // Space for errors
        )->when(
            $messageEligible,
            fn () => $this->{$messageType}($message),
        );

//        return match ($progress->state) {
//            'initial' => $this
//                ->box(
//                    title: $this->dim($this->truncate($label, $progress->terminal()->cols() - 6)),
//                    body: $this->dim($filled),
//                    color: 'blue',
//                    info: $progress->initialMessage,
//            ),
//            'submit' => $this
//                ->box(
//                    $this->dim($this->truncate($label, $progress->terminal()->cols() - 6)),
//                    $this->dim($filled),
//                    color: 'green',
//                    info: $info,
//                ),
//            'error' => $this
//                ->box(
//                    $this->truncate($label, $progress->terminal()->cols() - 6),
//                    $this->dim($filled),
//                    color: 'red',
//                    info: $info,
//                ),
//            'cancel' => $this
//                ->box(
//                    $this->truncate($label, $progress->terminal()->cols() - 6),
//                    $this->dim($filled),
//                    color: 'red',
//                    info: $info,
//                )
//                ->error($progress->cancelMessage),
//            default => $this
//                ->box(
//                    $this->cyan($this->truncate($label, $progress->terminal()->cols() - 6)),
//                    $this->dim($filled),
//                    color: 'yellow',
//                    info: $progress->progress.'/'.$progress->total,
//                )
//                ->when(
//                    $progress->hint,
//                    fn () => $this->hint($progress->hint),
//                    fn () => $this->newLine() // Space for errors
//                )
//        };
    }
}
