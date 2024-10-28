<?php

namespace App\Services;

use App\Prompts\Progress;
use App\Prompts\ProgressRenderer;
use App\Prompts\Spinner;
use App\Prompts\SpinnerRenderer;
use Illuminate\Support\Str;
use Laravel\Prompts\Clear;
use Laravel\Prompts\ConfirmPrompt;
use Laravel\Prompts\MultiSearchPrompt;
use Laravel\Prompts\MultiSelectPrompt;
use Laravel\Prompts\Note;
use Laravel\Prompts\PasswordPrompt;
use Laravel\Prompts\PausePrompt;
use Laravel\Prompts\SearchPrompt;
use Laravel\Prompts\SelectPrompt;
use Laravel\Prompts\SuggestPrompt;
use Laravel\Prompts\Table;
use Laravel\Prompts\TextareaPrompt;
use Laravel\Prompts\TextPrompt;
use Laravel\Prompts\Themes\Default\ClearRenderer;
use Laravel\Prompts\Themes\Default\ConfirmPromptRenderer;
use Laravel\Prompts\Themes\Default\MultiSearchPromptRenderer;
use Laravel\Prompts\Themes\Default\MultiSelectPromptRenderer;
use Laravel\Prompts\Themes\Default\NoteRenderer;
use Laravel\Prompts\Themes\Default\PasswordPromptRenderer;
use Laravel\Prompts\Themes\Default\PausePromptRenderer;
use Laravel\Prompts\Themes\Default\SearchPromptRenderer;
use Laravel\Prompts\Themes\Default\SelectPromptRenderer;
use Laravel\Prompts\Themes\Default\SuggestPromptRenderer;
use Laravel\Prompts\Themes\Default\TableRenderer;
use Laravel\Prompts\Themes\Default\TextareaPromptRenderer;
use Laravel\Prompts\Themes\Default\TextPromptRenderer;

final class Ntrn
{
    protected static array $init;

    public static function isInit(string $target = ''): bool
    {
        return ! blank($target) && isset(self::$init) ? isset(self::$init[$target]) : isset(self::$init);
    }

    public static function init(string $target): void
    {
        $method = "init{$target}";

        throw_if(! method_exists(self::class, "init{$target}"), new \Exception("Method [{$method}] does not exist"));

        if (isset(self::$init[$target])) {
            return;
        }

        self::{$method}();

        if (! isset(self::$init)) {
            self::$init = [];
        }

        self::$init[$target] = true;
    }

    private static function initPromptTheme(): void
    {
        \Laravel\Prompts\Prompt::addTheme('ntrn', [
            TextPrompt::class => TextPromptRenderer::class,
            TextareaPrompt::class => TextareaPromptRenderer::class,
            PasswordPrompt::class => PasswordPromptRenderer::class,
            SelectPrompt::class => SelectPromptRenderer::class,
            MultiSelectPrompt::class => MultiSelectPromptRenderer::class,
            ConfirmPrompt::class => ConfirmPromptRenderer::class,
            PausePrompt::class => PausePromptRenderer::class,
            SearchPrompt::class => SearchPromptRenderer::class,
            MultiSearchPrompt::class => MultiSearchPromptRenderer::class,
            SuggestPrompt::class => SuggestPromptRenderer::class,
            Spinner::class => SpinnerRenderer::class,
            Note::class => NoteRenderer::class,
            Table::class => TableRenderer::class,
            Progress::class => ProgressRenderer::class,
            Clear::class => ClearRenderer::class,
        ]);

        \Laravel\Prompts\Prompt::theme('ntrn');
    }
}
