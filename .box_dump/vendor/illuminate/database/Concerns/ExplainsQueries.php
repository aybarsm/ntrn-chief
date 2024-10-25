<?php

namespace Illuminate\Database\Concerns;

use Illuminate\Support\Collection;

trait ExplainsQueries
{





public function explain()
{
$sql = $this->toSql();

$bindings = $this->getBindings();

$explanation = $this->getConnection()->select('EXPLAIN '.$sql, $bindings);

return new Collection($explanation);
}
}
