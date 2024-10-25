<?php

namespace Illuminate\Database\Events;

class NoPendingMigrations
{





public $method;







public function __construct($method)
{
$this->method = $method;
}
}
