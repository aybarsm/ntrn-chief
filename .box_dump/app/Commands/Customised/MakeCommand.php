<?php

declare(strict_types=1);

namespace App\Commands\Customised;

use Illuminate\Foundation\Console\ConsoleMakeCommand;

class MakeCommand extends ConsoleMakeCommand
{
protected $signature = 'make:command
    {name : The name of the command}
    {--f|force : Create the class even if the console command already exists}
    {--command= : The terminal command that will be used to invoke the class}
    {--s|schedule : Create a new scheduled command}';
protected $description = 'Create a new Artisan command (Customised)';

protected function getNameInput(): string
{
return ucfirst(parent::getNameInput());
}

protected function getStub(): string
{
$relativePath = '/stubs/console' . ($this->option('schedule') ? '.schedule' : '') . '.stub';

return file_exists($customPath = $this->laravel->basePath(trim($relativePath, '/')))
? $customPath
: __DIR__.$relativePath;
}

protected function getDefaultNamespace($rootNamespace): string
{
return $rootNamespace.'\Commands';
}
}
