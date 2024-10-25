<?php

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:component')]
class ComponentMakeCommand extends GeneratorCommand
{
use CreatesMatchingTest;






protected $name = 'make:component';






protected $description = 'Create a new view component class';






protected $type = 'Component';






public function handle()
{
if ($this->option('view')) {
$this->writeView(function () {
$this->components->info($this->type.' created successfully.');
});

return;
}

if (parent::handle() === false && ! $this->option('force')) {
return false;
}

if (! $this->option('inline')) {
$this->writeView();
}
}







protected function writeView($onSuccess = null)
{
$path = $this->viewPath(
str_replace('.', '/', 'components.'.$this->getView()).'.blade.php'
);

if (! $this->files->isDirectory(dirname($path))) {
$this->files->makeDirectory(dirname($path), 0777, true, true);
}

if ($this->files->exists($path) && ! $this->option('force')) {
$this->components->error('View already exists.');

return;
}

file_put_contents(
$path,
'<div>
    <!-- '.Inspiring::quotes()->random().' -->
</div>'
);

if ($onSuccess) {
$onSuccess();
}
}







protected function buildClass($name)
{
if ($this->option('inline')) {
return str_replace(
['DummyView', '{{ view }}'],
"<<<'blade'\n<div>\n    <!-- ".Inspiring::quotes()->random()." -->\n</div>\nblade",
parent::buildClass($name)
);
}

return str_replace(
['DummyView', '{{ view }}'],
'view(\'components.'.$this->getView().'\')',
parent::buildClass($name)
);
}






protected function getView()
{
$name = str_replace('\\', '/', $this->argument('name'));

return collect(explode('/', $name))
->map(function ($part) {
return Str::kebab($part);
})
->implode('.');
}






protected function getStub()
{
return $this->resolveStubPath('/stubs/view-component.stub');
}







protected function resolveStubPath($stub)
{
return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
? $customPath
: __DIR__.$stub;
}







protected function getDefaultNamespace($rootNamespace)
{
return $rootNamespace.'\View\Components';
}






protected function getOptions()
{
return [
['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the component already exists'],
['inline', null, InputOption::VALUE_NONE, 'Create a component that renders an inline view'],
['view', null, InputOption::VALUE_NONE, 'Create an anonymous component with only a view'],
];
}
}
