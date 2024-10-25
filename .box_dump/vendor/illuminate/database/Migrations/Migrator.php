<?php

namespace Illuminate\Database\Migrations;

use Illuminate\Console\View\Components\BulletList;
use Illuminate\Console\View\Components\Info;
use Illuminate\Console\View\Components\Task;
use Illuminate\Console\View\Components\TwoColumnDetail;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Illuminate\Database\Events\MigrationEnded;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Database\Events\MigrationStarted;
use Illuminate\Database\Events\NoPendingMigrations;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Console\Output\OutputInterface;

class Migrator
{





protected $events;






protected $repository;






protected $files;






protected $resolver;






protected $connection;






protected $paths = [];






protected static $requiredPathCache = [];






protected $output;










public function __construct(MigrationRepositoryInterface $repository,
Resolver $resolver,
Filesystem $files,
?Dispatcher $dispatcher = null)
{
$this->files = $files;
$this->events = $dispatcher;
$this->resolver = $resolver;
$this->repository = $repository;
}








public function run($paths = [], array $options = [])
{



$files = $this->getMigrationFiles($paths);

$this->requireFiles($migrations = $this->pendingMigrations(
$files, $this->repository->getRan()
));




$this->runPending($migrations, $options);

return $migrations;
}








protected function pendingMigrations($files, $ran)
{
return Collection::make($files)
->reject(fn ($file) => in_array($this->getMigrationName($file), $ran))
->values()
->all();
}








public function runPending(array $migrations, array $options = [])
{



if (count($migrations) === 0) {
$this->fireMigrationEvent(new NoPendingMigrations('up'));

$this->write(Info::class, 'Nothing to migrate');

return;
}




$batch = $this->repository->getNextBatchNumber();

$pretend = $options['pretend'] ?? false;

$step = $options['step'] ?? false;

$this->fireMigrationEvent(new MigrationsStarted('up'));

$this->write(Info::class, 'Running migrations.');




foreach ($migrations as $file) {
$this->runUp($file, $batch, $pretend);

if ($step) {
$batch++;
}
}

$this->fireMigrationEvent(new MigrationsEnded('up'));

$this->output?->writeln('');
}









protected function runUp($file, $batch, $pretend)
{



$migration = $this->resolvePath($file);

$name = $this->getMigrationName($file);

if ($pretend) {
return $this->pretendToRun($migration, 'up');
}

$this->write(Task::class, $name, fn () => $this->runMigration($migration, 'up'));




$this->repository->log($name, $batch);
}








public function rollback($paths = [], array $options = [])
{



$migrations = $this->getMigrationsForRollback($options);

if (count($migrations) === 0) {
$this->fireMigrationEvent(new NoPendingMigrations('down'));

$this->write(Info::class, 'Nothing to rollback.');

return [];
}

return tap($this->rollbackMigrations($migrations, $paths, $options), function () {
$this->output?->writeln('');
});
}







protected function getMigrationsForRollback(array $options)
{
if (($steps = $options['step'] ?? 0) > 0) {
return $this->repository->getMigrations($steps);
}

if (($batch = $options['batch'] ?? 0) > 0) {
return $this->repository->getMigrationsByBatch($batch);
}

return $this->repository->getLast();
}









protected function rollbackMigrations(array $migrations, $paths, array $options)
{
$rolledBack = [];

$this->requireFiles($files = $this->getMigrationFiles($paths));

$this->fireMigrationEvent(new MigrationsStarted('down'));

$this->write(Info::class, 'Rolling back migrations.');




foreach ($migrations as $migration) {
$migration = (object) $migration;

if (! $file = Arr::get($files, $migration->migration)) {
$this->write(TwoColumnDetail::class, $migration->migration, '<fg=yellow;options=bold>Migration not found</>');

continue;
}

$rolledBack[] = $file;

$this->runDown(
$file, $migration,
$options['pretend'] ?? false
);
}

$this->fireMigrationEvent(new MigrationsEnded('down'));

return $rolledBack;
}








public function reset($paths = [], $pretend = false)
{



$migrations = array_reverse($this->repository->getRan());

if (count($migrations) === 0) {
$this->write(Info::class, 'Nothing to rollback.');

return [];
}

return tap($this->resetMigrations($migrations, Arr::wrap($paths), $pretend), function () {
$this->output?->writeln('');
});
}









protected function resetMigrations(array $migrations, array $paths, $pretend = false)
{



$migrations = collect($migrations)->map(fn ($m) => (object) ['migration' => $m])->all();

return $this->rollbackMigrations(
$migrations, $paths, compact('pretend')
);
}









protected function runDown($file, $migration, $pretend)
{



$instance = $this->resolvePath($file);

$name = $this->getMigrationName($file);

if ($pretend) {
return $this->pretendToRun($instance, 'down');
}

$this->write(Task::class, $name, fn () => $this->runMigration($instance, 'down'));




$this->repository->delete($migration);
}








protected function runMigration($migration, $method)
{
$connection = $this->resolveConnection(
$migration->getConnection()
);

$callback = function () use ($connection, $migration, $method) {
if (method_exists($migration, $method)) {
$this->fireMigrationEvent(new MigrationStarted($migration, $method));

$this->runMethod($connection, $migration, $method);

$this->fireMigrationEvent(new MigrationEnded($migration, $method));
}
};

$this->getSchemaGrammar($connection)->supportsSchemaTransactions()
&& $migration->withinTransaction
? $connection->transaction($callback)
: $callback();
}








protected function pretendToRun($migration, $method)
{
$name = get_class($migration);

$reflectionClass = new ReflectionClass($migration);

if ($reflectionClass->isAnonymous()) {
$name = $this->getMigrationName($reflectionClass->getFileName());
}

$this->write(TwoColumnDetail::class, $name);

$this->write(
BulletList::class,
collect($this->getQueries($migration, $method))->map(fn ($query) => $query['query'])
);
}








protected function getQueries($migration, $method)
{



$db = $this->resolveConnection(
$migration->getConnection()
);

return $db->pretend(function () use ($db, $migration, $method) {
if (method_exists($migration, $method)) {
$this->runMethod($db, $migration, $method);
}
});
}









protected function runMethod($connection, $migration, $method)
{
$previousConnection = $this->resolver->getDefaultConnection();

try {
$this->resolver->setDefaultConnection($connection->getName());

$migration->{$method}();
} finally {
$this->resolver->setDefaultConnection($previousConnection);
}
}







public function resolve($file)
{
$class = $this->getMigrationClass($file);

return new $class;
}







protected function resolvePath(string $path)
{
$class = $this->getMigrationClass($this->getMigrationName($path));

if (class_exists($class) && realpath($path) == (new ReflectionClass($class))->getFileName()) {
return new $class;
}

$migration = static::$requiredPathCache[$path] ??= $this->files->getRequire($path);

if (is_object($migration)) {
return method_exists($migration, '__construct')
? $this->files->getRequire($path)
: clone $migration;
}

return new $class;
}







protected function getMigrationClass(string $migrationName): string
{
return Str::studly(implode('_', array_slice(explode('_', $migrationName), 4)));
}







public function getMigrationFiles($paths)
{
return Collection::make($paths)
->flatMap(fn ($path) => str_ends_with($path, '.php') ? [$path] : $this->files->glob($path.'/*_*.php'))
->filter()
->values()
->keyBy(fn ($file) => $this->getMigrationName($file))
->sortBy(fn ($file, $key) => $key)
->all();
}







public function requireFiles(array $files)
{
foreach ($files as $file) {
$this->files->requireOnce($file);
}
}







public function getMigrationName($path)
{
return str_replace('.php', '', basename($path));
}







public function path($path)
{
$this->paths = array_unique(array_merge($this->paths, [$path]));
}






public function paths()
{
return $this->paths;
}






public function getConnection()
{
return $this->connection;
}








public function usingConnection($name, callable $callback)
{
$previousConnection = $this->resolver->getDefaultConnection();

$this->setConnection($name);

return tap($callback(), fn () => $this->setConnection($previousConnection));
}







public function setConnection($name)
{
if (! is_null($name)) {
$this->resolver->setDefaultConnection($name);
}

$this->repository->setSource($name);

$this->connection = $name;
}







public function resolveConnection($connection)
{
return $this->resolver->connection($connection ?: $this->connection);
}







protected function getSchemaGrammar($connection)
{
if (is_null($grammar = $connection->getSchemaGrammar())) {
$connection->useDefaultSchemaGrammar();

$grammar = $connection->getSchemaGrammar();
}

return $grammar;
}






public function getRepository()
{
return $this->repository;
}






public function repositoryExists()
{
return $this->repository->repositoryExists();
}






public function hasRunAnyMigrations()
{
return $this->repositoryExists() && count($this->repository->getRan()) > 0;
}






public function deleteRepository()
{
$this->repository->deleteRepository();
}






public function getFilesystem()
{
return $this->files;
}







public function setOutput(OutputInterface $output)
{
$this->output = $output;

return $this;
}








protected function write($component, ...$arguments)
{
if ($this->output && class_exists($component)) {
(new $component($this->output))->render(...$arguments);
} else {
foreach ($arguments as $argument) {
if (is_callable($argument)) {
$argument();
}
}
}
}







public function fireMigrationEvent($event)
{
$this->events?->dispatch($event);
}
}
