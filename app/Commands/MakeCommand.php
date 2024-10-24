<?php

namespace App\Commands;

use Illuminate\Foundation\Console\ConsoleMakeCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeCommand extends ConsoleMakeCommand
{
    protected $signature = 'make:command';
    protected $description = 'Create a new Artisan command (Customised)';

    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the console command already exists'],
            ['command', null, InputOption::VALUE_OPTIONAL, 'The terminal command that will be used to invoke the class'],
            ['schedule', 's', InputOption::VALUE_OPTIONAL, 'Create a new scheduled command'],
        ];
    }
}
