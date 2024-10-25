<?php

namespace Illuminate\Foundation\Testing;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Foundation\Testing\Traits\CanConfigureMigrationCommands;

trait DatabaseTruncation
{
use CanConfigureMigrationCommands;






protected static array $allTables;






protected function truncateDatabaseTables(): void
{
$this->beforeTruncatingDatabase();


if (! RefreshDatabaseState::$migrated) {
$this->artisan('migrate:fresh', $this->migrateFreshUsing());

$this->app[Kernel::class]->setArtisan(null);

RefreshDatabaseState::$migrated = true;

return;
}


$this->truncateTablesForAllConnections();

if ($seeder = $this->seeder()) {

$this->artisan('db:seed', ['--class' => $seeder]);
} elseif ($this->shouldSeed()) {

$this->artisan('db:seed');
}

$this->afterTruncatingDatabase();
}






protected function truncateTablesForAllConnections(): void
{
$database = $this->app->make('db');

collect($this->connectionsToTruncate())
->each(function ($name) use ($database) {
$connection = $database->connection($name);

$connection->getSchemaBuilder()->withoutForeignKeyConstraints(
fn () => $this->truncateTablesForConnection($connection, $name)
);
});
}








protected function truncateTablesForConnection(ConnectionInterface $connection, ?string $name): void
{
$dispatcher = $connection->getEventDispatcher();

$connection->unsetEventDispatcher();

collect(static::$allTables[$name] ??= $connection->getSchemaBuilder()->getTableListing())
->when(
property_exists($this, 'tablesToTruncate'),
fn ($tables) => $tables->intersect($this->tablesToTruncate),
fn ($tables) => $tables->diff($this->exceptTables($name))
)
->filter(fn ($table) => $connection->table($this->withoutTablePrefix($connection, $table))->exists())
->each(fn ($table) => $connection->table($this->withoutTablePrefix($connection, $table))->truncate());

$connection->setEventDispatcher($dispatcher);
}








protected function withoutTablePrefix(ConnectionInterface $connection, string $table)
{
$prefix = $connection->getTablePrefix();

return strpos($table, $prefix) === 0
? substr($table, strlen($prefix))
: $table;
}






protected function connectionsToTruncate(): array
{
return property_exists($this, 'connectionsToTruncate')
? $this->connectionsToTruncate : [null];
}







protected function exceptTables(?string $connectionName): array
{
$migrations = $this->app['config']->get('database.migrations');

$migrationsTable = is_array($migrations) ? ($migrations['table'] ?? null) : $migrations;

if (property_exists($this, 'exceptTables')) {
if (array_is_list($this->exceptTables ?? [])) {
return array_merge(
$this->exceptTables ?? [],
[$migrationsTable],
);
}

return array_merge(
$this->exceptTables[$connectionName] ?? [],
[$migrationsTable],
);
}

return [$migrationsTable];
}






protected function beforeTruncatingDatabase(): void
{

}






protected function afterTruncatingDatabase(): void
{

}
}
