<?php

namespace Illuminate\Database\Console\Migrations;

use Illuminate\Console\ConfirmableTrait;
use Illuminate\Console\Prohibitable;
use Illuminate\Database\Migrations\Migrator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'migrate:reset')]
class ResetCommand extends BaseCommand
{
use ConfirmableTrait, Prohibitable;






protected $name = 'migrate:reset';






protected $description = 'Rollback all database migrations';






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
return 1;
}

return $this->migrator->usingConnection($this->option('database'), function () {



if (! $this->migrator->repositoryExists()) {
return $this->components->warn('Migration table not found.');
}

$this->migrator->setOutput($this->output)->reset(
$this->getMigrationPaths(), $this->option('pretend')
);
});
}






protected function getOptions()
{
return [
['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use'],

['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],

['path', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The path(s) to the migrations files to be executed'],

['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths'],

['pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run'],
];
}
}
