<?php

namespace Illuminate\Database\Console\Migrations;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Console\Prohibitable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\DatabaseRefreshed;
use Illuminate\Database\Migrations\Migrator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'migrate:fresh')]
class FreshCommand extends Command
{
use ConfirmableTrait, Prohibitable;






protected $name = 'migrate:fresh';






protected $description = 'Drop all tables and re-run all migrations';






protected $migrator;







public function __construct(Migrator $migrator)
{
parent::__construct();

$this->migrator = $migrator;
}






public function handle()
{
if ($this->isProhibited() ||
! $this->confirmToProceed()) {
return Command::FAILURE;
}

$database = $this->input->getOption('database');

$this->migrator->usingConnection($database, function () use ($database) {
if ($this->migrator->repositoryExists()) {
$this->newLine();

$this->components->task('Dropping all tables', fn () => $this->callSilent('db:wipe', array_filter([
'--database' => $database,
'--drop-views' => $this->option('drop-views'),
'--drop-types' => $this->option('drop-types'),
'--force' => true,
])) == 0);
}
});

$this->newLine();

$this->call('migrate', array_filter([
'--database' => $database,
'--path' => $this->input->getOption('path'),
'--realpath' => $this->input->getOption('realpath'),
'--schema-path' => $this->input->getOption('schema-path'),
'--force' => true,
'--step' => $this->option('step'),
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
['drop-views', null, InputOption::VALUE_NONE, 'Drop all tables and views'],
['drop-types', null, InputOption::VALUE_NONE, 'Drop all tables and types (Postgres only)'],
['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],
['path', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The path(s) to the migrations files to be executed'],
['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths'],
['schema-path', null, InputOption::VALUE_OPTIONAL, 'The path to a schema dump file'],
['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run'],
['seeder', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder'],
['step', null, InputOption::VALUE_NONE, 'Force the migrations to be run so they can be rolled back individually'],
];
}
}
