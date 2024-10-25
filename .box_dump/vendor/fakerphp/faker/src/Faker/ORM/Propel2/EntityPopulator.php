<?php

namespace Faker\ORM\Propel2;

use Faker\Provider\Base;
use Propel\Runtime\Map\ColumnMap;




class EntityPopulator
{
protected $class;
protected $columnFormatters = [];
protected $modifiers = [];




public function __construct($class)
{
$this->class = $class;
}




public function getClass()
{
return $this->class;
}

public function setColumnFormatters($columnFormatters)
{
$this->columnFormatters = $columnFormatters;
}




public function getColumnFormatters()
{
return $this->columnFormatters;
}

public function mergeColumnFormattersWith($columnFormatters)
{
$this->columnFormatters = array_merge($this->columnFormatters, $columnFormatters);
}




public function guessColumnFormatters(\Faker\Generator $generator)
{
$formatters = [];
$class = $this->class;
$peerClass = $class::TABLE_MAP;
$tableMap = $peerClass::getTableMap();
$nameGuesser = new \Faker\Guesser\Name($generator);
$columnTypeGuesser = new \Faker\ORM\Propel2\ColumnTypeGuesser($generator);

foreach ($tableMap->getColumns() as $columnMap) {

if ($this->isColumnBehavior($columnMap)) {
continue;
}

if ($columnMap->isForeignKey()) {
$relatedClass = $columnMap->getRelation()->getForeignTable()->getClassname();
$formatters[$columnMap->getPhpName()] = static function ($inserted) use ($relatedClass, $generator) {
$relatedClass = trim($relatedClass, '\\');

return isset($inserted[$relatedClass]) ? $generator->randomElement($inserted[$relatedClass]) : null;
};

continue;
}

if ($columnMap->isPrimaryKey()) {
continue;
}

if ($formatter = $nameGuesser->guessFormat($columnMap->getPhpName(), $columnMap->getSize())) {
$formatters[$columnMap->getPhpName()] = $formatter;

continue;
}

if ($formatter = $columnTypeGuesser->guessFormat($columnMap)) {
$formatters[$columnMap->getPhpName()] = $formatter;

continue;
}
}

return $formatters;
}




protected function isColumnBehavior(ColumnMap $columnMap)
{
foreach ($columnMap->getTable()->getBehaviors() as $name => $params) {
$columnName = Base::toLower($columnMap->getName());

switch ($name) {
case 'nested_set':
$columnNames = [$params['left_column'], $params['right_column'], $params['level_column']];

if (in_array($columnName, $columnNames, false)) {
return true;
}

break;

case 'timestampable':
$columnNames = [$params['create_column'], $params['update_column']];

if (in_array($columnName, $columnNames, false)) {
return true;
}

break;
}
}

return false;
}

public function setModifiers($modifiers)
{
$this->modifiers = $modifiers;
}




public function getModifiers()
{
return $this->modifiers;
}

public function mergeModifiersWith($modifiers)
{
$this->modifiers = array_merge($this->modifiers, $modifiers);
}




public function guessModifiers(\Faker\Generator $generator)
{
$modifiers = [];
$class = $this->class;
$peerClass = $class::TABLE_MAP;
$tableMap = $peerClass::getTableMap();

foreach ($tableMap->getBehaviors() as $name => $params) {
switch ($name) {
case 'nested_set':
$modifiers['nested_set'] = static function ($obj, $inserted) use ($class, $generator): void {
if (isset($inserted[$class])) {
$queryClass = $class . 'Query';
$parent = $queryClass::create()->findPk($generator->randomElement($inserted[$class]));
$obj->insertAsLastChildOf($parent);
} else {
$obj->makeRoot();
}
};

break;

case 'sortable':
$modifiers['sortable'] = static function ($obj, $inserted) use ($class, $generator): void {
$obj->insertAtRank($generator->numberBetween(1, count($inserted[$class] ?? []) + 1));
};

break;
}
}

return $modifiers;
}




public function execute($con, $insertedEntities)
{
$obj = new $this->class();

foreach ($this->getColumnFormatters() as $column => $format) {
if (null !== $format) {
$obj->setByName($column, is_callable($format) ? $format($insertedEntities, $obj) : $format);
}
}

foreach ($this->getModifiers() as $modifier) {
$modifier($obj, $insertedEntities);
}
$obj->save($con);

return $obj->getPrimaryKey();
}
}
