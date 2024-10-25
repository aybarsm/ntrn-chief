<?php

declare(strict_types=1);

namespace Pest\Arch;

use Pest\Arch\Collections\Dependencies;
use Pest\Arch\Factories\LayerFactory;
use Pest\Arch\Options\LayerOptions;
use Pest\Arch\Repositories\ObjectsRepository;
use Pest\Arch\Support\AssertLocker;
use Pest\Arch\Support\Composer;
use Pest\Arch\Support\PhpCoreExpressions;
use Pest\Arch\ValueObjects\Dependency;
use Pest\Arch\ValueObjects\Targets;
use Pest\Arch\ValueObjects\Violation;
use Pest\TestSuite;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PHPUnit\Architecture\ArchitectureAsserts;
use PHPUnit\Architecture\Elements\ObjectDescription;
use PHPUnit\Architecture\Services\ServiceContainer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;








final class Blueprint
{
use ArchitectureAsserts;




public function __construct(
private readonly LayerFactory $layerFactory,
private readonly Targets $target,
private readonly Dependencies $dependencies
) {

}




public static function make(Targets $target, Dependencies $dependencies): self
{
$factory = new LayerFactory(ObjectsRepository::getInstance());

return new self($factory, $target, $dependencies);
}






public function expectToUse(LayerOptions $options, callable $failure): void
{
AssertLocker::incrementAndLock();

foreach ($this->target->value as $targetValue) {
$targetLayer = $this->layerFactory->make($options, $targetValue, false);

foreach ($this->dependencies->values as $dependency) {
$dependencyLayer = $this->layerFactory->make($options, $dependency->value);

try {
$this->assertDoesNotDependOn($targetLayer, $dependencyLayer);
} catch (ExpectationFailedException) {
continue;
}

$failure($targetValue, $dependency->value);
}
}

AssertLocker::unlock();
}








public function targeted(callable $callback, LayerOptions $options, callable $failure, callable $lineFinder): void
{
AssertLocker::incrementAndLock();

foreach ($this->target->value as $targetValue) {
$targetLayer = $this->layerFactory->make($options, $targetValue);

foreach ($targetLayer as $object) {
foreach ($options->exclude as $exclude) {
if (str_starts_with($object->name, $exclude)) {
continue 2;
}
}

if ($callback($object)) {
continue;
}

$path = (string) realpath($object->path);
$line = $lineFinder($path);
$path = substr($path, strlen(TestSuite::getInstance()->rootPath) + 1);

$failure(new Violation($path, $line, $line));
}
}

AssertLocker::unlock();
}






public function expectToOnlyUse(LayerOptions $options, callable $failure): void
{
AssertLocker::incrementAndLock();

foreach ($this->target->value as $targetValue) {
$allowedUses = array_merge(
...array_map(fn (Layer $layer): array => array_map(
fn (ObjectDescription $object): string => $object->name, iterator_to_array($layer->getIterator())), array_map(
fn (string $dependency): Layer => $this->layerFactory->make($options, $dependency),
[
$targetValue, ...array_map(
fn (Dependency $dependency): string => $dependency->value, $this->dependencies->values
),
],
)
));

$layer = $this->layerFactory->make($options, $targetValue);
foreach ($layer as $object) {
foreach ($object->uses as $use) {
if (! in_array($use, $allowedUses, true)) {
$failure($targetValue, $this->dependencies->__toString(), $use, $this->getUsagePathAndLines($layer, $targetValue, $use));

return;
}
}
}
}

AssertLocker::unlock();
}






public function expectToOnlyBeUsedIn(LayerOptions $options, callable $failure): void
{
AssertLocker::incrementAndLock();

foreach (Composer::userNamespaces() as $namespace) {
$namespaceLayer = $this->layerFactory->make($options, $namespace, false);

foreach ($this->dependencies->values as $dependency) {
$namespaceLayer = $namespaceLayer->excludeByNameStart($dependency->value);
}

foreach ($this->target->value as $targetValue) {
$dependencyLayer = $this->layerFactory->make($options, $targetValue);

try {
$this->assertDoesNotDependOn($namespaceLayer, $dependencyLayer);
} catch (ExpectationFailedException) {
$objects = $this->getObjectsWhichUsesOnLayerAFromLayerB($namespaceLayer, $dependencyLayer);
[$dependOn, $target] = explode(' <- ', $objects[0]);

$failure($target, $dependOn, $this->getUsagePathAndLines($namespaceLayer, $dependOn, $target));
}
}
}

AssertLocker::unlock();
}






public static function assertTrue(mixed $condition, string $message = ''): void
{
Assert::assertTrue($condition, $message);
}






public static function assertNotEquals(mixed $expected, mixed $actual, string $message = ''): void
{
Assert::assertNotEquals($expected, $actual, $message);
}






public static function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
{
Assert::assertEquals($expected, $actual, $message);
}

private function getUsagePathAndLines(Layer $layer, string $objectName, string $target): ?Violation
{
$dependOnObjects = array_filter(
$layer->getIterator()->getArrayCopy(), 
fn (ObjectDescription $objectDescription): bool => $objectDescription->name === $objectName
);


$dependOnObject = array_pop($dependOnObjects);

$class = PhpCoreExpressions::getClass($target) ?? Name::class;

$nodes = ServiceContainer::$nodeFinder->findInstanceOf(
$dependOnObject->stmts,
$class,
);


$names = array_values(array_filter(
$nodes, static function ($node) use ($target): bool {
$name = $node instanceof Name ? $node->toString() : PhpCoreExpressions::getName($node);

return $name === $target;
}
));

if ($names === []) {
return null;
}

$startLine = $names[0]->getAttribute('startLine');
assert(is_int($startLine));

$endLine = $names[0]->getAttribute('endLine');
assert(is_int($endLine));

$path = preg_replace('/[\/\\\\]vendor[\/\\\\]composer[\/\\\\]\.\.[\/\\\\]\.\./', '', $dependOnObject->path);

assert($path !== null);

return new Violation($path, $startLine, $endLine);
}
}
