<?php

namespace Illuminate\Database\Events;

use Illuminate\Contracts\Database\Events\MigrationEvent as MigrationEventContract;
use Illuminate\Database\Migrations\Migration;

abstract class MigrationEvent implements MigrationEventContract
{





public $migration;






public $method;








public function __construct(Migration $migration, $method)
{
$this->method = $method;
$this->migration = $migration;
}
}
