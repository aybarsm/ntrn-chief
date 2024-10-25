<?php declare(strict_types=1);








namespace PHPUnit\Metadata\Parser;

use function assert;
use function class_exists;
use function method_exists;
use PHPUnit\Metadata\MetadataCollection;

/**
@no-named-arguments


*/
final class CachingParser implements Parser
{
private readonly Parser $reader;
private array $classCache = [];
private array $methodCache = [];
private array $classAndMethodCache = [];

public function __construct(Parser $reader)
{
$this->reader = $reader;
}

/**
@psalm-param
*/
public function forClass(string $className): MetadataCollection
{
assert(class_exists($className));

if (isset($this->classCache[$className])) {
return $this->classCache[$className];
}

$this->classCache[$className] = $this->reader->forClass($className);

return $this->classCache[$className];
}

/**
@psalm-param
@psalm-param
*/
public function forMethod(string $className, string $methodName): MetadataCollection
{
assert(class_exists($className));
assert(method_exists($className, $methodName));

$key = $className . '::' . $methodName;

if (isset($this->methodCache[$key])) {
return $this->methodCache[$key];
}

$this->methodCache[$key] = $this->reader->forMethod($className, $methodName);

return $this->methodCache[$key];
}

/**
@psalm-param
@psalm-param
*/
public function forClassAndMethod(string $className, string $methodName): MetadataCollection
{
$key = $className . '::' . $methodName;

if (isset($this->classAndMethodCache[$key])) {
return $this->classAndMethodCache[$key];
}

$this->classAndMethodCache[$key] = $this->forClass($className)->mergeWith(
$this->forMethod($className, $methodName),
);

return $this->classAndMethodCache[$key];
}
}
