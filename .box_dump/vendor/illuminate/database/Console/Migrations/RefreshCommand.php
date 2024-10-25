<?php

namespace Illuminate\Database\Console\Migrations;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Console\Prohibitable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\DatabaseRefreshed;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'migrate:refresh')]
class RefreshCommand extends Command
{
use ConfirmableTrait, Prohibitable;






protected $name = 'migrate:refresh';






protected $description = 'Reset and re-run all migrations';






public function handle()
{
if ($this->isProhibited() ||
! $this->confirmToProceed()) {
return 1;
}




$database = $this->input->getOption('database');

$path = $this->input->getOption('path');




$step = $this->input->getOption('step') ?: 0;

if ($step > 0) {
$this->runRollback($database, $path, $step);
} else {
$this->runReset($database, $path);
}




$this->call('migrate', array_filter([
'--database' => $database,
'--path' => $path,
'--realpath' => $this->input->getOption('realpath'),
'--force' => true,
]));

if ($this->laravel->bound(Dispatcher::class)) {
$this->laravel[Dispatcher::class]->dispatch(
new DatabaseRefreshed($database, $this->needsSeeding())
);
}

if ($this->needsSeeding()) {
$this->runSeeder($database);
}

return 0;
}









protected function runRollback($database, $path, $step)
{
$this->call('migrate:rollback', array_filter([
'--database' => $database,
'--path' => $path,
'--realpath' => $this->input->getOption('realpath'),
'--step' => $step,
'--force' => true,
]));
}








protected function runReset($database, $path)
{
$this->call('migrate:reset', array_filter([
'--database' => $database,
'--path' => $path,
'--realpath' => $this->input->getOption('realpath'),
'--force' => true,
]));
}






protected function needsSeeding()
{
return $this->option('seed') || $this->option('seeder');
}







protected function runSeeder($database)
{
$this->call('db:seed', array_filter([
'--database' => $database,
'--class' => $this->option('seeder') ?: 'Database\\Seeders\\DatabaseSeeder',
'--force' => true,
]));
}






protected function getOptions()
{
return [
['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use'],
['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],
['path', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The path(s) to the migrations files to be executed'],
['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths'],
['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run'],
['seeder', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder'],
['step', null, InputOption::VALUE_OPTIONAL, 'The number of migrations to be reverted & re-run'],
];
}
}
