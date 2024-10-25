<?php

namespace Illuminate\Database\Query\Processors;

use Illuminate\Database\Query\Builder;

class MySqlProcessor extends Processor
{








public function processColumnListing($results)
{
return array_map(function ($result) {
return ((object) $result)->column_name;
}, $results);
}










public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
{
$query->getConnection()->insert($sql, $values, $sequence);

$id = $query->getConnection()->getLastInsertId();

return is_numeric($id) ? (int) $id : $id;
}







public function processColumns($results)
{
return array_map(function ($result) {
$result = (object) $result;

return [
'name' => $result->name,
'type_name' => $result->type_name,
'type' => $result->type,
'collation' => $result->collation,
'nullable' => $result->nullable === 'YES',
'default' => $result->default,
'auto_increment' => $result->extra === 'auto_increment',
'comment' => $result->comment ?: null,
'generation' => $result->expression ? [
'type' => match ($result->extra) {
'STORED GENERATED' => 'stored',
'VIRTUAL GENERATED' => 'virtual',
default => null,
},
'expression' => $result->expression,
] : null,
];
}, $results);
}







public function processIndexes($results)
{
return array_map(function ($result) {
$result = (object) $result;

return [
'name' => $name = strtolower($result->name),
'columns' => explode(',', $result->columns),
'type' => strtolower($result->type),
'unique' => (bool) $result->unique,
'primary' => $name === 'primary',
];
}, $results);
}







public function processForeignKeys($results)
{
return array_map(function ($result) {
$result = (object) $result;

return [
'name' => $result->name,
'columns' => explode(',', $result->columns),
'foreign_schema' => $result->foreign_schema,
'foreign_table' => $result->foreign_table,
'foreign_columns' => explode(',', $result->foreign_columns),
'on_update' => strtolower($result->on_update),
'on_delete' => strtolower($result->on_delete),
];
}, $results);
}
}
