<?php

declare(strict_types=1);

namespace App\Commands\Customised;

use Illuminate\Foundation\Console\ConsoleMakeCommand;
use Illuminate\Support\Str;

class MakeCommand extends ConsoleMakeCommand
{
    protected $signature = 'make:command
    {name : The name of the command}
    {--f|force : Create the class even if the console command already exists}
    {--command= : The terminal command that will be used to invoke the class}
    {--s|schedule : Create command with schedule}
    {--t|tasking : Create command with tasking}';

    protected $description = 'Create a new Artisan command (Customised)';

    protected function getNameInput(): string
    {
        return ucfirst(parent::getNameInput());
    }

    protected function getStub(): string
    {
        $relativePath = Str::of('/stubs/console')
            ->when($this->option('tasking'), fn ($path) => $path->append('.tasking'))
            ->when($this->option('schedule'), fn ($path) => $path->append('.schedule'))
            ->finish('.stub')->value();

        return file_exists($customPath = $this->laravel->basePath(trim($relativePath, '/')))
            ? $customPath
            : __DIR__.$relativePath;
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Commands';
    }
}
