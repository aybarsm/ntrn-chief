<?php

namespace Illuminate\Database\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Console\Prohibitable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'db:wipe')]
class WipeCommand extends Command
{
use ConfirmableTrait, Prohibitable;






protected $name = 'db:wipe';






protected $description = 'Drop all tables, views, and types';






public function handle()
{
if ($this->isProhibited() ||
! $this->confirmToProceed()) {
return Command::FAILURE;
}

$database = $this->input->getOption('database');

if ($this->option('drop-views')) {
$this->dropAllViews($database);

$this->components->info('Dropped all views successfully.');
}

$this->dropAllTables($database);

$this->components->info('Dropped all tables successfully.');

if ($this->option('drop-types')) {
$this->dropAllTypes($database);

$this->components->info('Dropped all types successfully.');
}

return 0;
}







protected function dropAllTables($database)
{
$this->laravel['db']->connection($database)
->getSchemaBuilder()
->dropAllTables();
}







protected function dropAllViews($database)
{
$this->laravel['db']->connection($database)
->getSchemaBuilder()
->dropAllViews();
}







protected function dropAllTypes($database)
{
$this->laravel['db']->connection($database)
->getSchemaBuilder()
->dropAllTypes();
}






protected function getOptions()
{
return [
['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use'],
['drop-views', null, InputOption::VALUE_NONE, 'Drop all tables and views'],
['drop-types', null, InputOption::VALUE_NONE, 'Drop all tables and types (Postgres only)'],
['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],
];
}
}
