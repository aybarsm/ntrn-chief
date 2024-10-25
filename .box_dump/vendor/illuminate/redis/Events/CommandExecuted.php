<?php

namespace Illuminate\Redis\Events;

class CommandExecuted
{





public $command;






public $parameters;






public $time;






public $connection;






public $connectionName;










public function __construct($command, $parameters, $time, $connection)
{
$this->time = $time;
$this->command = $command;
$this->parameters = $parameters;
$this->connection = $connection;
$this->connectionName = $connection->getName();
}
}
