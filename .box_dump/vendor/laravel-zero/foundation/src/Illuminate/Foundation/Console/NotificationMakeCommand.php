<?php

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:notification')]
class NotificationMakeCommand extends GeneratorCommand
{
use CreatesMatchingTest;






protected $name = 'make:notification';






protected $description = 'Create a new notification class';






protected $type = 'Notification';






public function handle()
{
if (parent::handle() === false && ! $this->option('force')) {
return;
}

if ($this->option('markdown')) {
$this->writeMarkdownTemplate();
}
}






protected function writeMarkdownTemplate()
{
$path = $this->viewPath(
str_replace('.', '/', $this->option('markdown')).'.blade.php'
);

if (! $this->files->isDirectory(dirname($path))) {
$this->files->makeDirectory(dirname($path), 0755, true);
}

$this->files->put($path, file_get_contents(__DIR__.'/stubs/markdown.stub'));
}







protected function buildClass($name)
{
$class = parent::buildClass($name);

if ($this->option('markdown')) {
$class = str_replace(['DummyView', '{{ view }}'], $this->option('markdown'), $class);
}

return $class;
}






protected function getStub()
{
return $this->option('markdown')
? $this->resolveStubPath('/stubs/markdown-notification.stub')
: $this->resolveStubPath('/stubs/notification.stub');
}







protected function resolveStubPath($stub)
{
return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
? $customPath
: __DIR__.$stub;
}







protected function getDefaultNamespace($rootNamespace)
{
return $rootNamespace.'\Notifications';
}






protected function getOptions()
{
return [
['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the notification already exists'],
['markdown', 'm', InputOption::VALUE_OPTIONAL, 'Create a new Markdown template for the notification'],
];
}
}
