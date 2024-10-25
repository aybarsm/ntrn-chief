<?php

namespace Faker\ORM\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Faker\Generator;

require_once 'backward-compatibility.php';





class Populator
{



protected $batchSize;




protected $generator;




protected $manager;




protected $entities = [];




protected $quantities = [];




protected $generateId = [];






public function __construct(Generator $generator, ObjectManager $manager = null, $batchSize = 1000)
{
$this->generator = $generator;
$this->manager = $manager;
$this->batchSize = $batchSize;
}







public function addEntity($entity, $number, $customColumnFormatters = [], $customModifiers = [], $generateId = false)
{
if (!$entity instanceof \Faker\ORM\Doctrine\EntityPopulator) {
if (null === $this->manager) {
throw new \InvalidArgumentException('No entity manager passed to Doctrine Populator.');
}
$entity = new \Faker\ORM\Doctrine\EntityPopulator($this->manager->getClassMetadata($entity));
}
$entity->setColumnFormatters($entity->guessColumnFormatters($this->generator));

if ($customColumnFormatters) {
$entity->mergeColumnFormattersWith($customColumnFormatters);
}
$entity->mergeModifiersWith($customModifiers);
$this->generateId[$entity->getClass()] = $generateId;

$class = $entity->getClass();
$this->entities[$class] = $entity;
$this->quantities[$class] = $number;
}











public function execute($entityManager = null)
{
if (null === $entityManager) {
$entityManager = $this->manager;
}

if (null === $entityManager) {
throw new \InvalidArgumentException('No entity manager passed to Doctrine Populator.');
}

$insertedEntities = [];

foreach ($this->quantities as $class => $number) {
$generateId = $this->generateId[$class];

for ($i = 0; $i < $number; ++$i) {
$insertedEntities[$class][] = $this->entities[$class]->execute(
$entityManager,
$insertedEntities,
$generateId,
);

if (count($insertedEntities) % $this->batchSize === 0) {
$entityManager->flush();
}
}
$entityManager->flush();
}

return $insertedEntities;
}
}
