<?php

namespace Illuminate\Foundation\Testing;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\Traits\CanConfigureMigrationCommands;

trait RefreshDatabase
{
use CanConfigureMigrationCommands;






public function refreshDatabase()
{
$this->beforeRefreshingDatabase();

if ($this->usingInMemoryDatabase()) {
$this->restoreInMemoryDatabase();
}

$this->refreshTestDatabase();

$this->afterRefreshingDatabase();
}






protected function usingInMemoryDatabase()
{
$default = config('database.default');

return config("database.connections.$default.database") === ':memory:';
}






protected function restoreInMemoryDatabase()
{
$database = $this->app->make('db');

foreach ($this->connectionsToTransact() as $name) {
if (isset(RefreshDatabaseState::$inMemoryConnections[$name])) {
$database->connection($name)->setPdo(RefreshDatabaseState::$inMemoryConnections[$name]);
}
}
}






protected function refreshTestDatabase()
{
if (! RefreshDatabaseState::$migrated) {
$this->artisan('migrate:fresh', $this->migrateFreshUsing());

$this->app[Kernel::class]->setArtisan(null);

RefreshDatabaseState::$migrated = true;
}

$this->beginDatabaseTransaction();
}






public function beginDatabaseTransaction()
{
$database = $this->app->make('db');

$this->app->instance('db.transactions', $transactionsManager = new DatabaseTransactionsManager);

foreach ($this->connectionsToTransact() as $name) {
$connection = $database->connection($name);

$connection->setTransactionManager($transactionsManager);

if ($this->usingInMemoryDatabase()) {
RefreshDatabaseState::$inMemoryConnections[$name] ??= $connection->getPdo();
}

$dispatcher = $connection->getEventDispatcher();

$connection->unsetEventDispatcher();
$connection->beginTransaction();
$connection->setEventDispatcher($dispatcher);
}

$this->beforeApplicationDestroyed(function () use ($database) {
foreach ($this->connectionsToTransact() as $name) {
$connection = $database->connection($name);
$dispatcher = $connection->getEventDispatcher();

$connection->unsetEventDispatcher();
$connection->rollBack();
$connection->setEventDispatcher($dispatcher);
$connection->disconnect();
}
});
}






protected function connectionsToTransact()
{
return property_exists($this, 'connectionsToTransact')
? $this->connectionsToTransact : [null];
}






protected function beforeRefreshingDatabase()
{

}






protected function afterRefreshingDatabase()
{

}
}
