<?php

namespace Faker\ORM\Propel;





class Populator
{
protected $generator;
protected $entities = [];
protected $quantities = [];

public function __construct(\Faker\Generator $generator)
{
$this->generator = $generator;
}







public function addEntity($entity, $number, $customColumnFormatters = [], $customModifiers = [])
{
if (!$entity instanceof \Faker\ORM\Propel\EntityPopulator) {
$entity = new \Faker\ORM\Propel\EntityPopulator($entity);
}
$entity->setColumnFormatters($entity->guessColumnFormatters($this->generator));

if ($customColumnFormatters) {
$entity->mergeColumnFormattersWith($customColumnFormatters);
}
$entity->setModifiers($entity->guessModifiers($this->generator));

if ($customModifiers) {
$entity->mergeModifiersWith($customModifiers);
}
$class = $entity->getClass();
$this->entities[$class] = $entity;
$this->quantities[$class] = $number;
}








public function execute($con = null)
{
if (null === $con) {
$con = $this->getConnection();
}
$isInstancePoolingEnabled = \Propel::isInstancePoolingEnabled();
\Propel::disableInstancePooling();
$insertedEntities = [];
$con->beginTransaction();

foreach ($this->quantities as $class => $number) {
for ($i = 0; $i < $number; ++$i) {
$insertedEntities[$class][] = $this->entities[$class]->execute($con, $insertedEntities);
}
}
$con->commit();

if ($isInstancePoolingEnabled) {
\Propel::enableInstancePooling();
}

return $insertedEntities;
}

protected function getConnection()
{

$class = key($this->entities);

if (!$class) {
throw new \RuntimeException('No class found from entities. Did you add entities to the Populator ?');
}

$peer = $class::PEER;

return \Propel::getConnection($peer::DATABASE_NAME, \Propel::CONNECTION_WRITE);
}
}
