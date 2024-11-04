<?php

namespace App\Framework\Commands;

use App\Prompts\Prompt;
use Illuminate\Support\Facades\Log;
use LaravelZero\Framework\Commands\Command as LaravelZeroCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends LaravelZeroCommand
{
    protected bool $loggerInit = false;

    protected function prompt($name, ...$params)
    {
        return Prompt::make($name, ...$params);
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
        $this->initLogger();

        return parent::run($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initLogger();

        return parent::execute($input, $output);
    }
}
