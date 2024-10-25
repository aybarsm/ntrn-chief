<?php

namespace Illuminate\Database\Console\Migrations;

use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Migrations\Migrator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand('migrate:rollback')]
class RollbackCommand extends BaseCommand
{
use ConfirmableTrait;






protected $name = 'migrate:rollback';






protected $description = 'Rollback the last database migration';






protected $migrator;







public function __construct(Migrator $migrator)
{
parent::__construct();

$this->migrator = $migrator;
}






public function handle()
{
if (! $this->confirmToProceed()) {
return 1;
}

$this->migrator->usingConnection($this->option('database'), function () {
$this->migrator->setOutput($this->output)->rollback(
$this->getMigrationPaths(), [
'pretend' => $this->option('pretend'),
'step' => (int) $this->option('step'),
'batch' => (int) $this->option('batch'),
]
);
});

return 0;
}






protected function getOptions()
{
return [
['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use'],
['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],
['path', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The path(s) to the migrations files to be executed'],
['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths'],
['pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run'],
['step', null, InputOption::VALUE_OPTIONAL, 'The number of migrations to be reverted'],
['batch', null, InputOption::VALUE_REQUIRED, 'The batch of migrations (identified by their batch number) to be reverted'],
];
}
}
