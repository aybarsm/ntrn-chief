<?php

namespace Illuminate\Database\Migrations;

use Closure;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MigrationCreator
{





protected $files;






protected $customStubPath;






protected $postCreate = [];








public function __construct(Filesystem $files, $customStubPath)
{
$this->files = $files;
$this->customStubPath = $customStubPath;
}












public function create($name, $path, $table = null, $create = false)
{
$this->ensureMigrationDoesntAlreadyExist($name, $path);




$stub = $this->getStub($table, $create);

$path = $this->getPath($name, $path);

$this->files->ensureDirectoryExists(dirname($path));

$this->files->put(
$path, $this->populateStub($stub, $table)
);




$this->firePostCreateHooks($table, $path);

return $path;
}










protected function ensureMigrationDoesntAlreadyExist($name, $migrationPath = null)
{
if (! empty($migrationPath)) {
$migrationFiles = $this->files->glob($migrationPath.'/*.php');

foreach ($migrationFiles as $migrationFile) {
$this->files->requireOnce($migrationFile);
}
}

if (class_exists($className = $this->getClassName($name))) {
throw new InvalidArgumentException("A {$className} class already exists.");
}
}








protected function getStub($table, $create)
{
if (is_null($table)) {
$stub = $this->files->exists($customPath = $this->customStubPath.'/migration.stub')
? $customPath
: $this->stubPath().'/migration.stub';
} elseif ($create) {
$stub = $this->files->exists($customPath = $this->customStubPath.'/migration.create.stub')
? $customPath
: $this->stubPath().'/migration.create.stub';
} else {
$stub = $this->files->exists($customPath = $this->customStubPath.'/migration.update.stub')
? $customPath
: $this->stubPath().'/migration.update.stub';
}

return $this->files->get($stub);
}








protected function populateStub($stub, $table)
{



if (! is_null($table)) {
$stub = str_replace(
['DummyTable', '{{ table }}', '{{table}}'],
$table, $stub
);
}

return $stub;
}







protected function getClassName($name)
{
return Str::studly($name);
}








protected function getPath($name, $path)
{
return $path.'/'.$this->getDatePrefix().'_'.$name.'.php';
}








protected function firePostCreateHooks($table, $path)
{
foreach ($this->postCreate as $callback) {
$callback($table, $path);
}
}







public function afterCreate(Closure $callback)
{
$this->postCreate[] = $callback;
}






protected function getDatePrefix()
{
return date('Y_m_d_His');
}






public function stubPath()
{
return __DIR__.'/stubs';
}






public function getFilesystem()
{
return $this->files;
}
}
