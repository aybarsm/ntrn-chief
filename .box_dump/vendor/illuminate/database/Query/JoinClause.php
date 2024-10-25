<?php

namespace Illuminate\Database\Query;

use Closure;

class JoinClause extends Builder
{





public $type;






public $table;






protected $parentConnection;






protected $parentGrammar;






protected $parentProcessor;






protected $parentClass;









public function __construct(Builder $parentQuery, $type, $table)
{
$this->type = $type;
$this->table = $table;
$this->parentClass = get_class($parentQuery);
$this->parentGrammar = $parentQuery->getGrammar();
$this->parentProcessor = $parentQuery->getProcessor();
$this->parentConnection = $parentQuery->getConnection();

parent::__construct(
$this->parentConnection, $this->parentGrammar, $this->parentProcessor
);
}





















public function on($first, $operator = null, $second = null, $boolean = 'and')
{
if ($first instanceof Closure) {
return $this->whereNested($first, $boolean);
}

return $this->whereColumn($first, $operator, $second, $boolean);
}









public function orOn($first, $operator = null, $second = null)
{
return $this->on($first, $operator, $second, 'or');
}






public function newQuery()
{
return new static($this->newParentQuery(), $this->type, $this->table);
}






protected function forSubQuery()
{
return $this->newParentQuery()->newQuery();
}






protected function newParentQuery()
{
$class = $this->parentClass;

return new $class($this->parentConnection, $this->parentGrammar, $this->parentProcessor);
}
}
