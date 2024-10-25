<?php

namespace Illuminate\Database;

use Closure;
use Exception;
use Illuminate\Database\Query\Grammars\SqlServerGrammar as QueryGrammar;
use Illuminate\Database\Query\Processors\SqlServerProcessor;
use Illuminate\Database\Schema\Grammars\SqlServerGrammar as SchemaGrammar;
use Illuminate\Database\Schema\SqlServerBuilder;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;
use Throwable;

class SqlServerConnection extends Connection
{



public function getDriverTitle()
{
return 'SQL Server';
}










public function transaction(Closure $callback, $attempts = 1)
{
for ($a = 1; $a <= $attempts; $a++) {
if ($this->getDriverName() === 'sqlsrv') {
return parent::transaction($callback, $attempts);
}

$this->getPdo()->exec('BEGIN TRAN');




try {
$result = $callback($this);

$this->getPdo()->exec('COMMIT TRAN');
}




catch (Throwable $e) {
$this->getPdo()->exec('ROLLBACK TRAN');

throw $e;
}

return $result;
}
}







protected function escapeBinary($value)
{
$hex = bin2hex($value);

return "0x{$hex}";
}







protected function isUniqueConstraintError(Exception $exception)
{
return boolval(preg_match('#Cannot insert duplicate key row in object#i', $exception->getMessage()));
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

return new SqlServerBuilder($this);
}






protected function getDefaultSchemaGrammar()
{
($grammar = new SchemaGrammar)->setConnection($this);

return $this->withTablePrefix($grammar);
}









public function getSchemaState(?Filesystem $files = null, ?callable $processFactory = null)
{
throw new RuntimeException('Schema dumping is not supported when using SQL Server.');
}






protected function getDefaultPostProcessor()
{
return new SqlServerProcessor;
}
}
