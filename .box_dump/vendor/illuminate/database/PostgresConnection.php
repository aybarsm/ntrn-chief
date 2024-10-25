<?php

namespace Illuminate\Database;

use Exception;
use Illuminate\Database\Query\Grammars\PostgresGrammar as QueryGrammar;
use Illuminate\Database\Query\Processors\PostgresProcessor;
use Illuminate\Database\Schema\Grammars\PostgresGrammar as SchemaGrammar;
use Illuminate\Database\Schema\PostgresBuilder;
use Illuminate\Database\Schema\PostgresSchemaState;
use Illuminate\Filesystem\Filesystem;

class PostgresConnection extends Connection
{



public function getDriverTitle()
{
return 'PostgreSQL';
}







protected function escapeBinary($value)
{
$hex = bin2hex($value);

return "'\x{$hex}'::bytea";
}







protected function escapeBool($value)
{
return $value ? 'true' : 'false';
}







protected function isUniqueConstraintError(Exception $exception)
{
return '23505' === $exception->getCode();
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

return new PostgresBuilder($this);
}






protected function getDefaultSchemaGrammar()
{
($grammar = new SchemaGrammar)->setConnection($this);

return $this->withTablePrefix($grammar);
}








public function getSchemaState(?Filesystem $files = null, ?callable $processFactory = null)
{
return new PostgresSchemaState($this, $files, $processFactory);
}






protected function getDefaultPostProcessor()
{
return new PostgresProcessor;
}
}
