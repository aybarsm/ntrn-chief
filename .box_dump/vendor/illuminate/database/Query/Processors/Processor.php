<?php

namespace Illuminate\Database\Query\Processors;

use Illuminate\Database\Query\Builder;

class Processor
{







public function processSelect(Builder $query, $results)
{
return $results;
}










public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
{
$query->getConnection()->insert($sql, $values);

$id = $query->getConnection()->getPdo()->lastInsertId($sequence);

return is_numeric($id) ? (int) $id : $id;
}







public function processTables($results)
{
return array_map(function ($result) {
$result = (object) $result;

return [
'name' => $result->name,
'schema' => $result->schema ?? null, 
'size' => isset($result->size) ? (int) $result->size : null,
'comment' => $result->comment ?? null, 
'collation' => $result->collation ?? null, 
'engine' => $result->engine ?? null, 
];
}, $results);
}







public function processViews($results)
{
return array_map(function ($result) {
$result = (object) $result;

return [
'name' => $result->name,
'schema' => $result->schema ?? null, 
'definition' => $result->definition,
];
}, $results);
}







public function processTypes($results)
{
return $results;
}







public function processColumns($results)
{
return $results;
}







public function processIndexes($results)
{
return $results;
}







public function processForeignKeys($results)
{
return $results;
}
}
