<?php

namespace Illuminate\Database;

use Carbon\CarbonInterval;
use Closure;
use DateTimeInterface;
use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\StatementPrepared;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionCommitting;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Grammars\Grammar as QueryGrammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Support\Traits\Macroable;
use PDO;
use PDOStatement;
use RuntimeException;

class Connection implements ConnectionInterface
{
use DetectsConcurrencyErrors,
DetectsLostConnections,
Concerns\ManagesTransactions,
InteractsWithTime,
Macroable;






protected $pdo;






protected $readPdo;






protected $database;






protected $readWriteType;






protected $tablePrefix = '';






protected $config = [];






protected $reconnector;






protected $queryGrammar;






protected $schemaGrammar;






protected $postProcessor;






protected $events;






protected $fetchMode = PDO::FETCH_OBJ;






protected $transactions = 0;






protected $transactionsManager;






protected $recordsModified = false;






protected $readOnWriteConnection = false;






protected $queryLog = [];






protected $loggingQueries = false;






protected $totalQueryDuration = 0.0;






protected $queryDurationHandlers = [];






protected $pretending = false;






protected $beforeStartingTransaction = [];






protected $beforeExecutingCallbacks = [];






protected static $resolvers = [];










public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
{
$this->pdo = $pdo;




$this->database = $database;

$this->tablePrefix = $tablePrefix;

$this->config = $config;




$this->useDefaultQueryGrammar();

$this->useDefaultPostProcessor();
}






public function useDefaultQueryGrammar()
{
$this->queryGrammar = $this->getDefaultQueryGrammar();
}






protected function getDefaultQueryGrammar()
{
($grammar = new QueryGrammar)->setConnection($this);

return $grammar;
}






public function useDefaultSchemaGrammar()
{
$this->schemaGrammar = $this->getDefaultSchemaGrammar();
}






protected function getDefaultSchemaGrammar()
{

}






public function useDefaultPostProcessor()
{
$this->postProcessor = $this->getDefaultPostProcessor();
}






protected function getDefaultPostProcessor()
{
return new Processor;
}






public function getSchemaBuilder()
{
if (is_null($this->schemaGrammar)) {
$this->useDefaultSchemaGrammar();
}

return new SchemaBuilder($this);
}








public function table($table, $as = null)
{
return $this->query()->from($table, $as);
}






public function query()
{
return new QueryBuilder(
$this, $this->getQueryGrammar(), $this->getPostProcessor()
);
}









public function selectOne($query, $bindings = [], $useReadPdo = true)
{
$records = $this->select($query, $bindings, $useReadPdo);

return array_shift($records);
}











public function scalar($query, $bindings = [], $useReadPdo = true)
{
$record = $this->selectOne($query, $bindings, $useReadPdo);

if (is_null($record)) {
return null;
}

$record = (array) $record;

if (count($record) > 1) {
throw new MultipleColumnsSelectedException;
}

return reset($record);
}








public function selectFromWriteConnection($query, $bindings = [])
{
return $this->select($query, $bindings, false);
}









public function select($query, $bindings = [], $useReadPdo = true)
{
return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
if ($this->pretending()) {
return [];
}




$statement = $this->prepared(
$this->getPdoForSelect($useReadPdo)->prepare($query)
);

$this->bindValues($statement, $this->prepareBindings($bindings));

$statement->execute();

return $statement->fetchAll();
});
}









public function selectResultSets($query, $bindings = [], $useReadPdo = true)
{
return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
if ($this->pretending()) {
return [];
}

$statement = $this->prepared(
$this->getPdoForSelect($useReadPdo)->prepare($query)
);

$this->bindValues($statement, $this->prepareBindings($bindings));

$statement->execute();

$sets = [];

do {
$sets[] = $statement->fetchAll();
} while ($statement->nextRowset());

return $sets;
});
}









public function cursor($query, $bindings = [], $useReadPdo = true)
{
$statement = $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
if ($this->pretending()) {
return [];
}




$statement = $this->prepared($this->getPdoForSelect($useReadPdo)
->prepare($query));

$this->bindValues(
$statement, $this->prepareBindings($bindings)
);




$statement->execute();

return $statement;
});

while ($record = $statement->fetch()) {
yield $record;
}
}







protected function prepared(PDOStatement $statement)
{
$statement->setFetchMode($this->fetchMode);

$this->event(new StatementPrepared($this, $statement));

return $statement;
}







protected function getPdoForSelect($useReadPdo = true)
{
return $useReadPdo ? $this->getReadPdo() : $this->getPdo();
}








public function insert($query, $bindings = [])
{
return $this->statement($query, $bindings);
}








public function update($query, $bindings = [])
{
return $this->affectingStatement($query, $bindings);
}








public function delete($query, $bindings = [])
{
return $this->affectingStatement($query, $bindings);
}








public function statement($query, $bindings = [])
{
return $this->run($query, $bindings, function ($query, $bindings) {
if ($this->pretending()) {
return true;
}

$statement = $this->getPdo()->prepare($query);

$this->bindValues($statement, $this->prepareBindings($bindings));

$this->recordsHaveBeenModified();

return $statement->execute();
});
}








public function affectingStatement($query, $bindings = [])
{
return $this->run($query, $bindings, function ($query, $bindings) {
if ($this->pretending()) {
return 0;
}




$statement = $this->getPdo()->prepare($query);

$this->bindValues($statement, $this->prepareBindings($bindings));

$statement->execute();

$this->recordsHaveBeenModified(
($count = $statement->rowCount()) > 0
);

return $count;
});
}







public function unprepared($query)
{
return $this->run($query, [], function ($query) {
if ($this->pretending()) {
return true;
}

$this->recordsHaveBeenModified(
$change = $this->getPdo()->exec($query) !== false
);

return $change;
});
}






public function threadCount()
{
$query = $this->getQueryGrammar()->compileThreadCount();

return $query ? $this->scalar($query) : null;
}







public function pretend(Closure $callback)
{
return $this->withFreshQueryLog(function () use ($callback) {
$this->pretending = true;




$callback($this);

$this->pretending = false;

return $this->queryLog;
});
}







public function withoutPretending(Closure $callback)
{
if (! $this->pretending) {
return $callback();
}

$this->pretending = false;

try {
return $callback();
} finally {
$this->pretending = true;
}
}







protected function withFreshQueryLog($callback)
{
$loggingQueries = $this->loggingQueries;




$this->enableQueryLog();

$this->queryLog = [];




$result = $callback();

$this->loggingQueries = $loggingQueries;

return $result;
}








public function bindValues($statement, $bindings)
{
foreach ($bindings as $key => $value) {
$statement->bindValue(
is_string($key) ? $key : $key + 1,
$value,
match (true) {
is_int($value) => PDO::PARAM_INT,
is_resource($value) => PDO::PARAM_LOB,
default => PDO::PARAM_STR
},
);
}
}







public function prepareBindings(array $bindings)
{
$grammar = $this->getQueryGrammar();

foreach ($bindings as $key => $value) {



if ($value instanceof DateTimeInterface) {
$bindings[$key] = $value->format($grammar->getDateFormat());
} elseif (is_bool($value)) {
$bindings[$key] = (int) $value;
}
}

return $bindings;
}











protected function run($query, $bindings, Closure $callback)
{
foreach ($this->beforeExecutingCallbacks as $beforeExecutingCallback) {
$beforeExecutingCallback($query, $bindings, $this);
}

$this->reconnectIfMissingConnection();

$start = microtime(true);




try {
$result = $this->runQueryCallback($query, $bindings, $callback);
} catch (QueryException $e) {
$result = $this->handleQueryException(
$e, $query, $bindings, $callback
);
}




$this->logQuery(
$query, $bindings, $this->getElapsedTime($start)
);

return $result;
}











protected function runQueryCallback($query, $bindings, Closure $callback)
{



try {
return $callback($query, $bindings);
}




catch (Exception $e) {
if ($this->isUniqueConstraintError($e)) {
throw new UniqueConstraintViolationException(
$this->getName(), $query, $this->prepareBindings($bindings), $e
);
}

throw new QueryException(
$this->getName(), $query, $this->prepareBindings($bindings), $e
);
}
}







protected function isUniqueConstraintError(Exception $exception)
{
return false;
}









public function logQuery($query, $bindings, $time = null)
{
$this->totalQueryDuration += $time ?? 0.0;

$this->event(new QueryExecuted($query, $bindings, $time, $this));

$query = $this->pretending === true
? $this->queryGrammar?->substituteBindingsIntoRawSql($query, $bindings) ?? $query
: $query;

if ($this->loggingQueries) {
$this->queryLog[] = compact('query', 'bindings', 'time');
}
}







protected function getElapsedTime($start)
{
return round((microtime(true) - $start) * 1000, 2);
}








public function whenQueryingForLongerThan($threshold, $handler)
{
$threshold = $threshold instanceof DateTimeInterface
? $this->secondsUntil($threshold) * 1000
: $threshold;

$threshold = $threshold instanceof CarbonInterval
? $threshold->totalMilliseconds
: $threshold;

$this->queryDurationHandlers[] = [
'has_run' => false,
'handler' => $handler,
];

$key = count($this->queryDurationHandlers) - 1;

$this->listen(function ($event) use ($threshold, $handler, $key) {
if (! $this->queryDurationHandlers[$key]['has_run'] && $this->totalQueryDuration() > $threshold) {
$handler($this, $event);

$this->queryDurationHandlers[$key]['has_run'] = true;
}
});
}






public function allowQueryDurationHandlersToRunAgain()
{
foreach ($this->queryDurationHandlers as $key => $queryDurationHandler) {
$this->queryDurationHandlers[$key]['has_run'] = false;
}
}






public function totalQueryDuration()
{
return $this->totalQueryDuration;
}






public function resetTotalQueryDuration()
{
$this->totalQueryDuration = 0.0;
}












protected function handleQueryException(QueryException $e, $query, $bindings, Closure $callback)
{
if ($this->transactions >= 1) {
throw $e;
}

return $this->tryAgainIfCausedByLostConnection(
$e, $query, $bindings, $callback
);
}












protected function tryAgainIfCausedByLostConnection(QueryException $e, $query, $bindings, Closure $callback)
{
if ($this->causedByLostConnection($e->getPrevious())) {
$this->reconnect();

return $this->runQueryCallback($query, $bindings, $callback);
}

throw $e;
}








public function reconnect()
{
if (is_callable($this->reconnector)) {
return call_user_func($this->reconnector, $this);
}

throw new LostConnectionException('Lost connection and no reconnector available.');
}






public function reconnectIfMissingConnection()
{
if (is_null($this->pdo)) {
$this->reconnect();
}
}






public function disconnect()
{
$this->setPdo(null)->setReadPdo(null);
}







public function beforeStartingTransaction(Closure $callback)
{
$this->beforeStartingTransaction[] = $callback;

return $this;
}







public function beforeExecuting(Closure $callback)
{
$this->beforeExecutingCallbacks[] = $callback;

return $this;
}







public function listen(Closure $callback)
{
$this->events?->listen(Events\QueryExecuted::class, $callback);
}







protected function fireConnectionEvent($event)
{
return $this->events?->dispatch(match ($event) {
'beganTransaction' => new TransactionBeginning($this),
'committed' => new TransactionCommitted($this),
'committing' => new TransactionCommitting($this),
'rollingBack' => new TransactionRolledBack($this),
default => null,
});
}







protected function event($event)
{
$this->events?->dispatch($event);
}







public function raw($value)
{
return new Expression($value);
}








public function escape($value, $binary = false)
{
if ($value === null) {
return 'null';
} elseif ($binary) {
return $this->escapeBinary($value);
} elseif (is_int($value) || is_float($value)) {
return (string) $value;
} elseif (is_bool($value)) {
return $this->escapeBool($value);
} elseif (is_array($value)) {
throw new RuntimeException('The database connection does not support escaping arrays.');
} else {
if (str_contains($value, "\00")) {
throw new RuntimeException('Strings with null bytes cannot be escaped. Use the binary escape option.');
}

if (preg_match('//u', $value) === false) {
throw new RuntimeException('Strings with invalid UTF-8 byte sequences cannot be escaped.');
}

return $this->escapeString($value);
}
}







protected function escapeString($value)
{
return $this->getReadPdo()->quote($value);
}







protected function escapeBool($value)
{
return $value ? '1' : '0';
}







protected function escapeBinary($value)
{
throw new RuntimeException('The database connection does not support escaping binary values.');
}






public function hasModifiedRecords()
{
return $this->recordsModified;
}







public function recordsHaveBeenModified($value = true)
{
if (! $this->recordsModified) {
$this->recordsModified = $value;
}
}







public function setRecordModificationState(bool $value)
{
$this->recordsModified = $value;

return $this;
}






public function forgetRecordModificationState()
{
$this->recordsModified = false;
}







public function useWriteConnectionWhenReading($value = true)
{
$this->readOnWriteConnection = $value;

return $this;
}






public function getPdo()
{
if ($this->pdo instanceof Closure) {
return $this->pdo = call_user_func($this->pdo);
}

return $this->pdo;
}






public function getRawPdo()
{
return $this->pdo;
}






public function getReadPdo()
{
if ($this->transactions > 0) {
return $this->getPdo();
}

if ($this->readOnWriteConnection ||
($this->recordsModified && $this->getConfig('sticky'))) {
return $this->getPdo();
}

if ($this->readPdo instanceof Closure) {
return $this->readPdo = call_user_func($this->readPdo);
}

return $this->readPdo ?: $this->getPdo();
}






public function getRawReadPdo()
{
return $this->readPdo;
}







public function setPdo($pdo)
{
$this->transactions = 0;

$this->pdo = $pdo;

return $this;
}







public function setReadPdo($pdo)
{
$this->readPdo = $pdo;

return $this;
}







public function setReconnector(callable $reconnector)
{
$this->reconnector = $reconnector;

return $this;
}






public function getName()
{
return $this->getConfig('name');
}






public function getNameWithReadWriteType()
{
return $this->getName().($this->readWriteType ? '::'.$this->readWriteType : '');
}







public function getConfig($option = null)
{
return Arr::get($this->config, $option);
}






public function getDriverName()
{
return $this->getConfig('driver');
}






public function getDriverTitle()
{
return $this->getDriverName();
}






public function getQueryGrammar()
{
return $this->queryGrammar;
}







public function setQueryGrammar(Query\Grammars\Grammar $grammar)
{
$this->queryGrammar = $grammar;

return $this;
}






public function getSchemaGrammar()
{
return $this->schemaGrammar;
}







public function setSchemaGrammar(Schema\Grammars\Grammar $grammar)
{
$this->schemaGrammar = $grammar;

return $this;
}






public function getPostProcessor()
{
return $this->postProcessor;
}







public function setPostProcessor(Processor $processor)
{
$this->postProcessor = $processor;

return $this;
}






public function getEventDispatcher()
{
return $this->events;
}







public function setEventDispatcher(Dispatcher $events)
{
$this->events = $events;

return $this;
}






public function unsetEventDispatcher()
{
$this->events = null;
}







public function setTransactionManager($manager)
{
$this->transactionsManager = $manager;

return $this;
}






public function unsetTransactionManager()
{
$this->transactionsManager = null;
}






public function pretending()
{
return $this->pretending === true;
}






public function getQueryLog()
{
return $this->queryLog;
}






public function getRawQueryLog()
{
return array_map(fn (array $log) => [
'raw_query' => $this->queryGrammar->substituteBindingsIntoRawSql(
$log['query'],
$this->prepareBindings($log['bindings'])
),
'time' => $log['time'],
], $this->getQueryLog());
}






public function flushQueryLog()
{
$this->queryLog = [];
}






public function enableQueryLog()
{
$this->loggingQueries = true;
}






public function disableQueryLog()
{
$this->loggingQueries = false;
}






public function logging()
{
return $this->loggingQueries;
}






public function getDatabaseName()
{
return $this->database;
}







public function setDatabaseName($database)
{
$this->database = $database;

return $this;
}







public function setReadWriteType($readWriteType)
{
$this->readWriteType = $readWriteType;

return $this;
}






public function getTablePrefix()
{
return $this->tablePrefix;
}







public function setTablePrefix($prefix)
{
$this->tablePrefix = $prefix;

$this->getQueryGrammar()->setTablePrefix($prefix);

return $this;
}

/**
@template





*/
public function withTablePrefix(Grammar $grammar)
{
$grammar->setTablePrefix($this->tablePrefix);

return $grammar;
}






public function getServerVersion(): string
{
return $this->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION);
}








public static function resolverFor($driver, Closure $callback)
{
static::$resolvers[$driver] = $callback;
}







public static function getResolver($driver)
{
return static::$resolvers[$driver] ?? null;
}
}
