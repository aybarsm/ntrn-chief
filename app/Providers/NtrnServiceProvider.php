<?php

namespace App\Providers;

use App\Prompts\Progress;
use App\Prompts\Spinner;
use App\Prompts\Themes\Ntrn\ProgressRenderer;
use App\Prompts\Themes\Ntrn\SpinnerRenderer;
use App\Traits\Configable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
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

class NtrnServiceProvider extends ServiceProvider
{
    use Configable;

    public function register(): void
    {
        App::booted(function () {
            static::initPromptTheme();
        });
    }

    public function boot(): void
    {
        $this->loadMixins();
    }

    /** @noinspection PhpUnreachableStatementInspection */
    protected function addMixin(string $mixin): void
    {
        throw_if(blank($mixin), new \Exception('Mixin class name cannot be empty.'));

        $cnfKey = str($mixin)
            ->replace('\\', '_')
            ->lower()
            ->prepend('mixins.loaded.')
            ->value();

        if ($this->config('has', $cnfKey)) {
            return;
        }

        throw_if(! class_exists($mixin), "Mixin class [{$mixin}] not found.");

        $docComment = (new \ReflectionClass($mixin))?->getDocComment();
        throw_if($docComment === false, "Mixin class [{$mixin}] does not have a doc comment.");

        $bind = Str::match(config('ntrn.mixins.pattern', '/@mixin\s*([^\s*]+)/'), $docComment);
        throw_if(blank($bind), "Mixin class [{$mixin}] does not have a pattern eligible bind.");
        throw_if(! class_exists($bind), "Mixin [{$mixin}] class bind of [{$bind}] not found.");
        throw_if(! method_exists($bind, 'mixin'), "Mixin [{$mixin}] class bind of [{$bind}] does not have a mixin method.");

        $bind::mixin(new $mixin, (bool) config('ntrn.mixins.replace', true));
        $this->config('set', $cnfKey, true);
    }

    protected function loadMixins(): void
    {
        foreach (config('ntrn.mixins.list', []) as $mixin) {
            $this->addMixin($mixin);
        }
    }

    protected static function initPromptTheme(): void
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
