<?php

namespace Illuminate\Database\Console\Migrations;

use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\SchemaLoaded;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\SQLiteDatabaseDoesNotExistException;
use Illuminate\Database\SqlServerConnection;
use PDOException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;

use function Laravel\Prompts\confirm;

#[AsCommand(name: 'migrate')]
class MigrateCommand extends BaseCommand implements Isolatable
{
use ConfirmableTrait;






protected $signature = 'migrate {--database= : The database connection to use}
                {--force : Force the operation to run when in production}
                {--path=* : The path(s) to the migrations files to be executed}
                {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
                {--schema-path= : The path to a schema dump file}
                {--pretend : Dump the SQL queries that would be run}
                {--seed : Indicates if the seed task should be re-run}
                {--seeder= : The class name of the root seeder}
                {--step : Force the migrations to be run so they can be rolled back individually}
                {--graceful : Return a successful exit code even if an error occurs}';






protected $description = 'Run the database migrations';






protected $migrator;






protected $dispatcher;








public function __construct(Migrator $migrator, Dispatcher $dispatcher)
{
parent::__construct();

$this->migrator = $migrator;
$this->dispatcher = $dispatcher;
}






public function handle()
{
if (! $this->confirmToProceed()) {
return 1;
}

try {
$this->runMigrations();
} catch (Throwable $e) {
if ($this->option('graceful')) {
$this->components->warn($e->getMessage());

return 0;
}

throw $e;
}

return 0;
}






protected function runMigrations()
{
$this->migrator->usingConnection($this->option('database'), function () {
$this->prepareDatabase();




$this->migrator->setOutput($this->output)
->run($this->getMigrationPaths(), [
'pretend' => $this->option('pretend'),
'step' => $this->option('step'),
]);




if ($this->option('seed') && ! $this->option('pretend')) {
$this->call('db:seed', [
'--class' => $this->option('seeder') ?: 'Database\\Seeders\\DatabaseSeeder',
'--force' => true,
]);
}
});
}






protected function prepareDatabase()
{
if (! $this->repositoryExists()) {
$this->components->info('Preparing database.');

$this->components->task('Creating migration table', function () {
return $this->callSilent('migrate:install', array_filter([
'--database' => $this->option('database'),
])) == 0;
});

$this->newLine();
}

if (! $this->migrator->hasRunAnyMigrations() && ! $this->option('pretend')) {
$this->loadSchemaState();
}
}






protected function repositoryExists()
{
return retry(2, fn () => $this->migrator->repositoryExists(), 0, function ($e) {
try {
if ($e->getPrevious() instanceof SQLiteDatabaseDoesNotExistException) {
return $this->createMissingSqliteDatabase($e->getPrevious()->path);
}

$connection = $this->migrator->resolveConnection($this->option('database'));

if (
$e->getPrevious() instanceof PDOException &&
$e->getPrevious()->getCode() === 1049 &&
in_array($connection->getDriverName(), ['mysql', 'mariadb'])) {
return $this->createMissingMysqlDatabase($connection);
}

return false;
} catch (Throwable) {
return false;
}
});
}









protected function createMissingSqliteDatabase($path)
{
if ($this->option('force')) {
return touch($path);
}

if ($this->option('no-interaction')) {
return false;
}

$this->components->warn('The SQLite database configured for this application does not exist: '.$path);

if (! confirm('Would you like to create it?', default: true)) {
$this->components->info('Operation cancelled. No database was created.');

throw new RuntimeException('Database was not created. Aborting migration.');
}

return touch($path);
}








protected function createMissingMysqlDatabase($connection)
{
if ($this->laravel['config']->get("database.connections.{$connection->getName()}.database") !== $connection->getDatabaseName()) {
return false;
}

if (! $this->option('force') && $this->option('no-interaction')) {
return false;
}

if (! $this->option('force') && ! $this->option('no-interaction')) {
$this->components->warn("The database '{$connection->getDatabaseName()}' does not exist on the '{$connection->getName()}' connection.");

if (! confirm('Would you like to create it?', default: true)) {
$this->components->info('Operation cancelled. No database was created.');

throw new RuntimeException('Database was not created. Aborting migration.');
}
}

try {
$this->laravel['config']->set("database.connections.{$connection->getName()}.database", null);

$this->laravel['db']->purge();

$freshConnection = $this->migrator->resolveConnection($this->option('database'));

return tap($freshConnection->unprepared("CREATE DATABASE IF NOT EXISTS `{$connection->getDatabaseName()}`"), function () {
$this->laravel['db']->purge();
});
} finally {
$this->laravel['config']->set("database.connections.{$connection->getName()}.database", $connection->getDatabaseName());
}
}






protected function loadSchemaState()
{
$connection = $this->migrator->resolveConnection($this->option('database'));




if ($connection instanceof SqlServerConnection ||
! is_file($path = $this->schemaPath($connection))) {
return;
}

$this->components->info('Loading stored database schemas.');

$this->components->task($path, function () use ($connection, $path) {



$this->migrator->deleteRepository();

$connection->getSchemaState()->handleOutputUsing(function ($type, $buffer) {
$this->output->write($buffer);
})->load($path);
});

$this->newLine();




$this->dispatcher->dispatch(
new SchemaLoaded($connection, $path)
);
}







protected function schemaPath($connection)
{
if ($this->option('schema-path')) {
return $this->option('schema-path');
}

if (file_exists($path = database_path('schema/'.$connection->getName().'-schema.dump'))) {
return $path;
}

return database_path('schema/'.$connection->getName().'-schema.sql');
}
}
