<?php

namespace Illuminate\Database;

use Exception;
use Illuminate\Database\Query\Grammars\MySqlGrammar as QueryGrammar;
use Illuminate\Database\Query\Processors\MySqlProcessor;
use Illuminate\Database\Schema\Grammars\MySqlGrammar as SchemaGrammar;
use Illuminate\Database\Schema\MySqlBuilder;
use Illuminate\Database\Schema\MySqlSchemaState;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use PDO;

class MySqlConnection extends Connection
{





protected $lastInsertId;




public function getDriverTitle()
{
return $this->isMaria() ? 'MariaDB' : 'MySQL';
}









public function insert($query, $bindings = [], $sequence = null)
{
return $this->run($query, $bindings, function ($query, $bindings) use ($sequence) {
if ($this->pretending()) {
return true;
}

$statement = $this->getPdo()->prepare($query);

$this->bindValues($statement, $this->prepareBindings($bindings));

$this->recordsHaveBeenModified();

$result = $statement->execute();

$this->lastInsertId = $this->getPdo()->lastInsertId($sequence);

return $result;
});
}







protected function escapeBinary($value)
{
$hex = bin2hex($value);

return "x'{$hex}'";
}







protected function isUniqueConstraintError(Exception $exception)
{
return boolval(preg_match('#Integrity constraint violation: 1062#i', $exception->getMessage()));
}






public function getLastInsertId()
{
return $this->lastInsertId;
}






public function isMaria()
{
return str_contains($this->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION), 'MariaDB');
}






public function getServerVersion(): string
{
return str_contains($version = parent::getServerVersion(), 'MariaDB')
? Str::between($version, '5.5.5-', '-MariaDB')
: $version;
}






protected function getDefaultQueryGrammar()
{
($grammar = new QueryGrammar)->setConnection($this);

return $this->withTablePrefix($grammar);
}






public function getSchemaBuilder()
{
if (is_null($this->schemaGrammar)) {
$this->useDefaultSchemaGrammar();
}

return new MySqlBuilder($this);
}






protected function getDefaultSchemaGrammar()
{
($grammar = new SchemaGrammar)->setConnection($this);

return $this->withTablePrefix($grammar);
}








public function getSchemaState(?Filesystem $files = null, ?callable $processFactory = null)
{
return new MySqlSchemaState($this, $files, $processFactory);
}






protected function getDefaultPostProcessor()
{
return new MySqlProcessor;
}
}
