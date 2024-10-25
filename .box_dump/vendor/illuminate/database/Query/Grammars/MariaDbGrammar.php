<?php

namespace Illuminate\Database\Query\Grammars;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinLateralClause;
use RuntimeException;

class MariaDbGrammar extends MySqlGrammar
{









public function compileJoinLateral(JoinLateralClause $join, string $expression): string
{
throw new RuntimeException('This database engine does not support lateral joins.');
}







public function compileJsonValueCast($value)
{
return "json_query({$value}, '$')";
}






public function compileThreadCount()
{
return 'select variable_value as `Value` from information_schema.global_status where variable_name = \'THREADS_CONNECTED\'';
}







public function useLegacyGroupLimit(Builder $query)
{
return false;
}
}
