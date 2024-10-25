<?php

namespace Illuminate\Database\Events;

class DatabaseBusy
{





public $connectionName;






public $connections;







public function __construct($connectionName, $connections)
{
$this->connectionName = $connectionName;
$this->connections = $connections;
}
}
