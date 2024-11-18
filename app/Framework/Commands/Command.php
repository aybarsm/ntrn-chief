<?php

namespace App\Framework\Commands;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command as LaravelZeroCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends LaravelZeroCommand
{
    protected bool $loggerInit = false;

    protected function prompt($name, ...$params): mixed
    {
        $name = Str::of($name)
            ->lower()
            ->replaceMatches('/[^A-Za-z0-9]/', '')
            ->chopEnd('prompt')
            ->value();

        return match ($name) {
            'clear' => new \App\Prompts\Clear,
            'confirm' => new \App\Prompts\ConfirmPrompt(...$params),
            'formbuilder' => new \App\Prompts\FormBuilder(...$params),
            'formstep' => new \App\Prompts\FormStep(...$params),
            'multisearch' => new \App\Prompts\MultiSearchPrompt(...$params),
            'multiselect' => new \App\Prompts\MultiSelectPrompt(...$params),
            'note' => new \App\Prompts\Note(...$params),
            'error' => new \App\Prompts\Note(...(array_merge($params, ['type' => 'error']))),
            'warning' => new \App\Prompts\Note(...(array_merge($params, ['type' => 'warning']))),
            'alert' => new \App\Prompts\Note(...(array_merge($params, ['type' => 'alert']))),
            'info' => new \App\Prompts\Note(...(array_merge($params, ['type' => 'info']))),
            'intro' => new \App\Prompts\Note(...(array_merge($params, ['type' => 'intro']))),
            'outro' => new \App\Prompts\Note(...(array_merge($params, ['type' => 'outro']))),
            'password' => new \App\Prompts\PasswordPrompt(...$params),
            'pause' => new \App\Prompts\PausePrompt(...$params),
            'progress' => new \App\Prompts\Progress(...$params),
            'search' => new \App\Prompts\SearchPrompt(...$params),
            'select' => new \App\Prompts\SelectPrompt(...$params),
            'suggest' => new \App\Prompts\SuggestPrompt(...$params),
            'table' => new \App\Prompts\Table(...$params),
            'terminal' => new \App\Prompts\Terminal,
            'textarea' => new \App\Prompts\TextareaPrompt(...$params),
            'text' => new \App\Prompts\TextPrompt(...$params),
            'flowingoutput' => new \App\Prompts\FlowingOutput(...$params),
            default => throw new \InvalidArgumentException("Prompt [{$name}] not found."),
        };
    }

    public function task(string $title = '', $task = null, $loadingText = 'loading...'): bool
    {
        return $this->__call('task', func_get_args());
    }

    protected function initLogger(): void
    {
        if ($this->loggerInit) {
            return;
        }

        Log::withContext([
            'command' => [
                'class' => get_class($this),
                'signature' => $this->signature,
            ],
        ]);

        $this->loggerInit = true;
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        //        if ($output->getVerbosity() === OutputInterface::VERBOSITY_NORMAL){
        //            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        //        }

        $this->initLogger();

        return parent::run($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        //        if ($output->getVerbosity() === OutputInterface::VERBOSITY_NORMAL){
        //            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        //        }

        $this->initLogger();

        return parent::execute($input, $output);
    }
}
