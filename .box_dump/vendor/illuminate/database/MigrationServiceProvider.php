<?php

namespace Illuminate\Database;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Database\Console\Migrations\InstallCommand;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Illuminate\Database\Console\Migrations\RefreshCommand;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Database\Console\Migrations\RollbackCommand;
use Illuminate\Database\Console\Migrations\StatusCommand;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\ServiceProvider;

class MigrationServiceProvider extends ServiceProvider implements DeferrableProvider
{





protected $commands = [
'Migrate' => MigrateCommand::class,
'MigrateFresh' => FreshCommand::class,
'MigrateInstall' => InstallCommand::class,
'MigrateRefresh' => RefreshCommand::class,
'MigrateReset' => ResetCommand::class,
'MigrateRollback' => RollbackCommand::class,
'MigrateStatus' => StatusCommand::class,
'MigrateMake' => MigrateMakeCommand::class,
];






public function register()
{
$this->registerRepository();

$this->registerMigrator();

$this->registerCreator();

$this->registerCommands($this->commands);
}






protected function registerRepository()
{
$this->app->singleton('migration.repository', function ($app) {
$migrations = $app['config']['database.migrations'];

$table = is_array($migrations) ? ($migrations['table'] ?? null) : $migrations;

return new DatabaseMigrationRepository($app['db'], $table);
});
}






protected function registerMigrator()
{



$this->app->singleton('migrator', function ($app) {
$repository = $app['migration.repository'];

return new Migrator($repository, $app['db'], $app['files'], $app['events']);
});
}






protected function registerCreator()
{
$this->app->singleton('migration.creator', function ($app) {
return new MigrationCreator($app['files'], $app->basePath('stubs'));
});
}







protected function registerCommands(array $commands)
{
foreach (array_keys($commands) as $command) {
$this->{"register{$command}Command"}();
}

$this->commands(array_values($commands));
}






protected function registerMigrateCommand()
{
$this->app->singleton(MigrateCommand::class, function ($app) {
return new MigrateCommand($app['migrator'], $app[Dispatcher::class]);
});
}






protected function registerMigrateFreshCommand()
{
$this->app->singleton(FreshCommand::class, function ($app) {
return new FreshCommand($app['migrator']);
});
}






protected function registerMigrateInstallCommand()
{
$this->app->singleton(InstallCommand::class, function ($app) {
return new InstallCommand($app['migration.repository']);
});
}






protected function registerMigrateMakeCommand()
{
$this->app->singleton(MigrateMakeCommand::class, function ($app) {



$creator = $app['migration.creator'];

$composer = $app['composer'];

return new MigrateMakeCommand($creator, $composer);
});
}






protected function registerMigrateRefreshCommand()
{
$this->app->singleton(RefreshCommand::class);
}






protected function registerMigrateResetCommand()
{
$this->app->singleton(ResetCommand::class, function ($app) {
return new ResetCommand($app['migrator']);
});
}






protected function registerMigrateRollbackCommand()
{
$this->app->singleton(RollbackCommand::class, function ($app) {
return new RollbackCommand($app['migrator']);
});
}






protected function registerMigrateStatusCommand()
{
$this->app->singleton(StatusCommand::class, function ($app) {
return new StatusCommand($app['migrator']);
});
}






public function provides()
{
return array_merge([
'migrator', 'migration.repository', 'migration.creator',
], array_values($this->commands));
}
}
