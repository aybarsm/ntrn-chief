<?php

namespace Illuminate\Database;

use Exception;
use Illuminate\Database\Query\Grammars\SQLiteGrammar as QueryGrammar;
use Illuminate\Database\Query\Processors\SQLiteProcessor;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar as SchemaGrammar;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Database\Schema\SqliteSchemaState;
use Illuminate\Filesystem\Filesystem;

class SQLiteConnection extends Connection
{









public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
{
parent::__construct($pdo, $database, $tablePrefix, $config);

$this->configureForeignKeyConstraints();
$this->configureBusyTimeout();
$this->configureJournalMode();
$this->configureSynchronous();
}




public function getDriverTitle()
{
return 'SQLite';
}






protected function configureForeignKeyConstraints(): void
{
$enableForeignKeyConstraints = $this->getConfig('foreign_key_constraints');

if ($enableForeignKeyConstraints === null) {
return;
}

$schemaBuilder = $this->getSchemaBuilder();

try {
$enableForeignKeyConstraints
? $schemaBuilder->enableForeignKeyConstraints()
: $schemaBuilder->disableForeignKeyConstraints();
} catch (QueryException $e) {
if (! $e->getPrevious() instanceof SQLiteDatabaseDoesNotExistException) {
throw $e;
}
}
}






protected function configureBusyTimeout(): void
{
$milliseconds = $this->getConfig('busy_timeout');

if ($milliseconds === null) {
return;
}

try {
$this->getSchemaBuilder()->setBusyTimeout($milliseconds);
} catch (QueryException $e) {
if (! $e->getPrevious() instanceof SQLiteDatabaseDoesNotExistException) {
throw $e;
}
}
}






protected function configureJournalMode(): void
{
$mode = $this->getConfig('journal_mode');

if ($mode === null) {
return;
}

try {
$this->getSchemaBuilder()->setJournalMode($mode);
} catch (QueryException $e) {
if (! $e->getPrevious() instanceof SQLiteDatabaseDoesNotExistException) {
throw $e;
}
}
}






protected function configureSynchronous(): void
{
$mode = $this->getConfig('synchronous');

if ($mode === null) {
return;
}

try {
$this->getSchemaBuilder()->setSynchronous($mode);
} catch (QueryException $e) {
if (! $e->getPrevious() instanceof SQLiteDatabaseDoesNotExistException) {
throw $e;
}
}
}







protected function escapeBinary($value)
{
$hex = bin2hex($value);

return "x'{$hex}'";
}







protected function isUniqueConstraintError(Exception $exception)
{
return boolval(preg_match('#(column(s)? .* (is|are) not unique|UNIQUE constraint failed: .*)#i', $exception->getMessage()));
}






protected function getDefaultQueryGrammar()
{
($grammar = new QueryGrammar)->setConnection($this);

return $this->withTablePrefix($grammar);
}






public function getSchemaBuilder()
{
if (is_null($this->schemaGrammar)) {
$this->useDefaultSchemaGrammar();
}

return new SQLiteBuilder($this);
}






protected function getDefaultSchemaGrammar()
{
($grammar = new SchemaGrammar)->setConnection($this);

return $this->withTablePrefix($grammar);
}









public function getSchemaState(?Filesystem $files = null, ?callable $processFactory = null)
{
return new SqliteSchemaState($this, $files, $processFactory);
}






protected function getDefaultPostProcessor()
{
return new SQLiteProcessor;
}
}
