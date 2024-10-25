<?php

namespace Illuminate\Database\Console\Seeds;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'db:seed')]
class SeedCommand extends Command
{
use ConfirmableTrait;






protected $name = 'db:seed';






protected $description = 'Seed the database with records';






protected $resolver;







public function __construct(Resolver $resolver)
{
parent::__construct();

$this->resolver = $resolver;
}






public function handle()
{
if (! $this->confirmToProceed()) {
return 1;
}

$this->components->info('Seeding database.');

$previousConnection = $this->resolver->getDefaultConnection();

$this->resolver->setDefaultConnection($this->getDatabase());

Model::unguarded(function () {
$this->getSeeder()->__invoke();
});

if ($previousConnection) {
$this->resolver->setDefaultConnection($previousConnection);
}

return 0;
}






protected function getSeeder()
{
$class = $this->input->getArgument('class') ?? $this->input->getOption('class');

if (! str_contains($class, '\\')) {
$class = 'Database\\Seeders\\'.$class;
}

if ($class === 'Database\\Seeders\\DatabaseSeeder' &&
! class_exists($class)) {
$class = 'DatabaseSeeder';
}

return $this->laravel->make($class)
->setContainer($this->laravel)
->setCommand($this);
}






protected function getDatabase()
{
$database = $this->input->getOption('database');

return $database ?: $this->laravel['config']['database.default'];
}






protected function getArguments()
{
return [
['class', InputArgument::OPTIONAL, 'The class name of the root seeder', null],
];
}






protected function getOptions()
{
return [
['class', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder', 'Database\\Seeders\\DatabaseSeeder'],
['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to seed'],
['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],
];
}
}