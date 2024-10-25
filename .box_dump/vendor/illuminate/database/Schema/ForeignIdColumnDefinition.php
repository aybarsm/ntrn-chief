<?php

namespace Illuminate\Database\Schema;

use Illuminate\Support\Str;

class ForeignIdColumnDefinition extends ColumnDefinition
{





protected $blueprint;








public function __construct(Blueprint $blueprint, $attributes = [])
{
parent::__construct($attributes);

$this->blueprint = $blueprint;
}









public function constrained($table = null, $column = 'id', $indexName = null)
{
$table ??= $this->table;

return $this->references($column, $indexName)->on($table ?? Str::of($this->name)->beforeLast('_'.$column)->plural());
}








public function references($column, $indexName = null)
{
return $this->blueprint->foreign($this->name, $indexName)->references($column);
}
}
