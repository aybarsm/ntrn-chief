<?php

namespace Illuminate\Database\Console\Factories;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:factory')]
class FactoryMakeCommand extends GeneratorCommand
{





protected $name = 'make:factory';






protected $description = 'Create a new model factory';






protected $type = 'Factory';






protected function getStub()
{
return $this->resolveStubPath('/stubs/factory.stub');
}







protected function resolveStubPath($stub)
{
return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
? $customPath
: __DIR__.$stub;
}







protected function buildClass($name)
{
$factory = class_basename(Str::ucfirst(str_replace('Factory', '', $name)));

$namespaceModel = $this->option('model')
? $this->qualifyModel($this->option('model'))
: $this->qualifyModel($this->guessModelName($name));

$model = class_basename($namespaceModel);

$namespace = $this->getNamespace(
Str::replaceFirst($this->rootNamespace(), 'Database\\Factories\\', $this->qualifyClass($this->getNameInput()))
);

$replace = [
'{{ factoryNamespace }}' => $namespace,
'NamespacedDummyModel' => $namespaceModel,
'{{ namespacedModel }}' => $namespaceModel,
'{{namespacedModel}}' => $namespaceModel,
'DummyModel' => $model,
'{{ model }}' => $model,
'{{model}}' => $model,
'{{ factory }}' => $factory,
'{{factory}}' => $factory,
];

return str_replace(
array_keys($replace), array_values($replace), parent::buildClass($name)
);
}







protected function getPath($name)
{
$name = (string) Str::of($name)->replaceFirst($this->rootNamespace(), '')->finish('Factory');

return $this->laravel->databasePath().'/factories/'.str_replace('\\', '/', $name).'.php';
}







protected function guessModelName($name)
{
if (str_ends_with($name, 'Factory')) {
$name = substr($name, 0, -7);
}

$modelName = $this->qualifyModel(Str::after($name, $this->rootNamespace()));

if (class_exists($modelName)) {
return $modelName;
}

if (is_dir(app_path('Models/'))) {
return $this->rootNamespace().'Models\Model';
}

return $this->rootNamespace().'Model';
}






protected function getOptions()
{
return [
['model', 'm', InputOption::VALUE_OPTIONAL, 'The name of the model'],
];
}
}
