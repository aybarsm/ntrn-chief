<?php

namespace App\Prompts\Themes\Ntrn;

use App\Prompts\Progress;
use Illuminate\Support\Number;
use Laravel\Prompts\Themes\Default\Concerns\DrawsBoxes;
use Laravel\Prompts\Themes\Default\Renderer as LaravelRenderer;

class ProgressRenderer extends LaravelRenderer
{
    use DrawsBoxes;

    protected string $barCharacter = '█';

    public function __invoke(Progress $progress): string
    {

        $completed = $progress->progress;
        $total = $progress->total;

        $numberType = $progress->config('get', 'number.type', null);
        $numberOptions = $progress->config('get', 'number.options', []);

        if ($numberType !== null && method_exists(Number::class, $numberType)) {
            $completed = Number::{$numberType}($completed, ...$numberOptions);
            $total = Number::{$numberType}($total, ...$numberOptions);
        }

        $hint = $progress->config('get', ["state.{$progress->state}.hint.value", 'state.default.hint.value'], '');
        $info = $progress->config('get', ["state.{$progress->state}.info.value", 'state.default.info.value'], "{$completed} / {$total}");
        $color = $progress->config('get', ["state.{$progress->state}.color", 'state.default.color'], 'yellow');

        $label = $progress->config('get', ["state.{$progress->state}.label.value", 'state.default.label.value'], '');
        $showPercentage = $progress->config('get', 'show.percentage', true);
        $label = $showPercentage ? $label.' '.round($progress->percentage() * 100).'%' : $label;
        $labelMethod = $progress->config('get', ["state.{$progress->state}.label.method", 'state.default.label.method'], 'cyan');
        if ($labelMethod !== null && method_exists($this, $labelMethod)) {
            $label = $this->{$labelMethod}($label);
        }

        $message = $progress->config('get', ["state.{$progress->state}.message.value", 'state.default.message.value'], null);
        $messageMethod = $progress->config('get', ["state.{$progress->state}.message.method", 'state.default.message.method'], null);
        $messageEligible = $message !== null && $messageMethod !== null && method_exists($this, $messageMethod);

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
            fn () => $this->{$messageMethod}($message),
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
