<?php

namespace Illuminate\Database\Events;

class QueryExecuted
{





public $sql;






public $bindings;






public $time;






public $connection;






public $connectionName;










public function __construct($sql, $bindings, $time, $connection)
{
$this->sql = $sql;
$this->time = $time;
$this->bindings = $bindings;
$this->connection = $connection;
$this->connectionName = $connection->getName();
}






public function toRawSql()
{
return $this->connection
->query()
->getGrammar()
->substituteBindingsIntoRawSql($this->sql, $this->connection->prepareBindings($this->bindings));
}
}
