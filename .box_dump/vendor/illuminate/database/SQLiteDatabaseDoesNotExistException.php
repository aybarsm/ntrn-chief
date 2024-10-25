<?php

namespace Illuminate\Database;

use InvalidArgumentException;

class SQLiteDatabaseDoesNotExistException extends InvalidArgumentException
{





public $path;







public function __construct($path)
{
parent::__construct("Database file at path [{$path}] does not exist. Ensure this is an absolute path to the database.");

$this->path = $path;
}
}
