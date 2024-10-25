<?php

namespace Illuminate\Database\Events;

class SchemaDumped
{





public $connection;






public $connectionName;






public $path;








public function __construct($connection, $path)
{
$this->connection = $connection;
$this->connectionName = $connection->getName();
$this->path = $path;
}
}
