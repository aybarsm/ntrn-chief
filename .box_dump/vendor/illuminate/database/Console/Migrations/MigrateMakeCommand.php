<?php

namespace Illuminate\Database\Console\Migrations;

use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:migration')]
class MigrateMakeCommand extends BaseCommand implements PromptsForMissingInput
{





protected $signature = 'make:migration {name : The name of the migration}
        {--create= : The table to be created}
        {--table= : The table to migrate}
        {--path= : The location where the migration file should be created}
        {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
        {--fullpath : Output the full path of the migration (Deprecated)}';






protected $description = 'Create a new migration file';






protected $creator;








protected $composer;








public function __construct(MigrationCreator $creator, Composer $composer)
{
parent::__construct();

$this->creator = $creator;
$this->composer = $composer;
}






public function handle()
{



$name = Str::snake(trim($this->input->getArgument('name')));

$table = $this->input->getOption('table');

$create = $this->input->getOption('create') ?: false;




if (! $table && is_string($create)) {
$table = $create;

$create = true;
}




if (! $table) {
[$table, $create] = TableGuesser::guess($name);
}




$this->writeMigration($name, $table, $create);
}









protected function writeMigration($name, $table, $create)
{
$file = $this->creator->create(
$name, $this->getMigrationPath(), $table, $create
);

$this->components->info(sprintf('Migration [%s] created successfully.', $file));
}






protected function getMigrationPath()
{
if (! is_null($targetPath = $this->input->getOption('path'))) {
return ! $this->usingRealPath()
? $this->laravel->basePath().'/'.$targetPath
: $targetPath;
}

return parent::getMigrationPath();
}






protected function promptForMissingArgumentsUsing()
{
return [
'name' => ['What should the migration be named?', 'E.g. create_flights_table'],
];
}
}
