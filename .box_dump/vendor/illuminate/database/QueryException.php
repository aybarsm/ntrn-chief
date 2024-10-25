<?php

namespace Illuminate\Database;

use Illuminate\Support\Str;
use PDOException;
use Throwable;

class QueryException extends PDOException
{





public $connectionName;






protected $sql;






protected $bindings;










public function __construct($connectionName, $sql, array $bindings, Throwable $previous)
{
parent::__construct('', 0, $previous);

$this->connectionName = $connectionName;
$this->sql = $sql;
$this->bindings = $bindings;
$this->code = $previous->getCode();
$this->message = $this->formatMessage($connectionName, $sql, $bindings, $previous);

if ($previous instanceof PDOException) {
$this->errorInfo = $previous->errorInfo;
}
}










protected function formatMessage($connectionName, $sql, $bindings, Throwable $previous)
{
return $previous->getMessage().' (Connection: '.$connectionName.', SQL: '.Str::replaceArray('?', $bindings, $sql).')';
}






public function getConnectionName()
{
return $this->connectionName;
}






public function getSql()
{
return $this->sql;
}






public function getBindings()
{
return $this->bindings;
}
}
