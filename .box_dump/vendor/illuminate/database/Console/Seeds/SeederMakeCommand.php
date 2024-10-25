<?php

namespace Illuminate\Database\Console\Seeds;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:seeder')]
class SeederMakeCommand extends GeneratorCommand
{





protected $name = 'make:seeder';






protected $description = 'Create a new seeder class';






protected $type = 'Seeder';






public function handle()
{
parent::handle();
}






protected function getStub()
{
return $this->resolveStubPath('/stubs/seeder.stub');
}







protected function resolveStubPath($stub)
{
return is_file($customPath = $this->laravel->basePath(trim($stub, '/')))
? $customPath
: __DIR__.$stub;
}







protected function getPath($name)
{
$name = str_replace('\\', '/', Str::replaceFirst($this->rootNamespace(), '', $name));

if (is_dir($this->laravel->databasePath().'/seeds')) {
return $this->laravel->databasePath().'/seeds/'.$name.'.php';
}

return $this->laravel->databasePath().'/seeders/'.$name.'.php';
}






protected function rootNamespace()
{
return 'Database\Seeders\\';
}
}
