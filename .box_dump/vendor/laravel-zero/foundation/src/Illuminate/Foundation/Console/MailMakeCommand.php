<?php

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:mail')]
class MailMakeCommand extends GeneratorCommand
{
use CreatesMatchingTest;






protected $name = 'make:mail';






protected $description = 'Create a new email class';






protected $type = 'Mailable';






public function handle()
{
if (parent::handle() === false && ! $this->option('force')) {
return;
}

if ($this->option('markdown') !== false) {
$this->writeMarkdownTemplate();
}
}






protected function writeMarkdownTemplate()
{
$path = $this->viewPath(
str_replace('.', '/', $this->getView()).'.blade.php'
);

if (! $this->files->isDirectory(dirname($path))) {
$this->files->makeDirectory(dirname($path), 0755, true);
}

$this->files->put($path, file_get_contents(__DIR__.'/stubs/markdown.stub'));
}







protected function buildClass($name)
{
$class = str_replace(
'{{ subject }}',
Str::headline(str_replace($this->getNamespace($name).'\\', '', $name)),
parent::buildClass($name)
);

if ($this->option('markdown') !== false) {
$class = str_replace(['DummyView', '{{ view }}'], $this->getView(), $class);
}

return $class;
}






protected function getView()
{
$view = $this->option('markdown');

if (! $view) {
$name = str_replace('\\', '/', $this->argument('name'));

$view = 'mail.'.collect(explode('/', $name))
->map(fn ($part) => Str::kebab($part))
->implode('.');
}

return $view;
}






protected function getStub()
{
return $this->resolveStubPath(
$this->option('markdown') !== false
? '/stubs/markdown-mail.stub'
: '/stubs/mail.stub');
}







protected function resolveStubPath($stub)
{
return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
? $customPath
: __DIR__.$stub;
}







protected function getDefaultNamespace($rootNamespace)
{
return $rootNamespace.'\Mail';
}






protected function getOptions()
{
return [
['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the mailable already exists'],
['markdown', 'm', InputOption::VALUE_OPTIONAL, 'Create a new Markdown template for the mailable', false],
];
}
}
