<?php

namespace Illuminate\Database;

class DatabaseTransactionRecord
{





public $connection;






public $level;






public $parent;






protected $callbacks = [];









public function __construct($connection, $level, ?DatabaseTransactionRecord $parent = null)
{
$this->connection = $connection;
$this->level = $level;
$this->parent = $parent;
}







public function addCallback($callback)
{
$this->callbacks[] = $callback;
}






public function executeCallbacks()
{
foreach ($this->callbacks as $callback) {
$callback();
}
}






public function getCallbacks()
{
return $this->callbacks;
}
}
