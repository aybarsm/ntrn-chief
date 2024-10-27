<?php

namespace App\Providers;

use App\Framework\Prompts\Spinner;
use App\Framework\Prompts\SpinnerRenderer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Laravel\Prompts\Clear;
use Laravel\Prompts\ConfirmPrompt;
use Laravel\Prompts\MultiSearchPrompt;
use Laravel\Prompts\MultiSelectPrompt;
use Laravel\Prompts\Note;
use Laravel\Prompts\PasswordPrompt;
use Laravel\Prompts\PausePrompt;
use Laravel\Prompts\Progress;
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
use Laravel\Prompts\Themes\Default\ProgressRenderer;
use Laravel\Prompts\Themes\Default\SearchPromptRenderer;
use Laravel\Prompts\Themes\Default\SelectPromptRenderer;
use Laravel\Prompts\Themes\Default\SuggestPromptRenderer;
use Laravel\Prompts\Themes\Default\TableRenderer;
use Laravel\Prompts\Themes\Default\TextareaPromptRenderer;
use Laravel\Prompts\Themes\Default\TextPromptRenderer;

class AppServiceProvider extends ServiceProvider
{

    public function boot(): void
    {

    }

    public function register(): void
    {
        Http::macro('progress', function ($callback) {
            return $this->withOptions([
                'progress' => fn (...$parameters) => $callback(...$parameters),
            ]);
        });

        App::booted(function () {
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
        });

//        App::booted(function () {
//            Command::macro(
//                'task',
//                function (string $title, $task = null, $loadingText = 'loading ZZZZ...') {
//                    $this->output->write("$title: <comment>{$loadingText}</comment>");
//
//                    if ($task === null) {
//                        $result = true;
//                    } else {
//                        try {
//                            $result = $task() === false ? false : true;
//                        } catch (\Exception $taskException) {
//                            $result = false;
//                        }
//                    }
//
//                    if ($this->output->isDecorated()) { // Determines if we can use escape sequences
//                        // Move the cursor to the beginning of the line
//                        $this->output->write("\x0D");
//
//                        // Erase the line
//                        $this->output->write("\x1B[2K");
//                    } else {
//                        $this->output->writeln(''); // Make sure we first close the previous line
//                    }
//
//                    $this->output->writeln(
//                        "$title: ".($result ? '<info>✔</info>' : '<error>failed</error>')
//                    );
//
//                    if (isset($taskException)) {
//                        throw $taskException;
//                    }
//
//                    return $result;
//                }
//            );
//        });
    }
}
