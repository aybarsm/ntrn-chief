<?php declare(strict_types=1);








namespace PHPUnit\TestRunner\TestResult;

use function array_merge;
use function assert;
use function in_array;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Framework\TestSize\Known;
use PHPUnit\Framework\TestSize\TestSize;
use PHPUnit\Metadata\Api\Groups;

/**
@no-named-arguments


*/
final class PassedTests
{
private static ?self $instance = null;

/**
@psalm-var
*/
private array $passedTestClasses = [];

/**
@psalm-var
*/
private array $passedTestMethods = [];

public static function instance(): self
{
if (self::$instance !== null) {
return self::$instance;
}

self::$instance = new self;

return self::$instance;
}

/**
@psalm-param
*/
public function testClassPassed(string $className): void
{
$this->passedTestClasses[] = $className;
}

public function testMethodPassed(TestMethod $test, mixed $returnValue): void
{
$size = (new Groups)->size(
$test->className(),
$test->methodName(),
);

$this->passedTestMethods[$test->className() . '::' . $test->methodName()] = [
'returnValue' => $returnValue,
'size' => $size,
];
}

public function import(self $other): void
{
$this->passedTestClasses = array_merge(
$this->passedTestClasses,
$other->passedTestClasses,
);

$this->passedTestMethods = array_merge(
$this->passedTestMethods,
$other->passedTestMethods,
);
}

/**
@psalm-param
*/
public function hasTestClassPassed(string $className): bool
{
return in_array($className, $this->passedTestClasses, true);
}

public function hasTestMethodPassed(string $method): bool
{
return isset($this->passedTestMethods[$method]);
}

public function isGreaterThan(string $method, TestSize $other): bool
{
if ($other->isUnknown()) {
return false;
}

assert($other instanceof Known);

$size = $this->passedTestMethods[$method]['size'];

if ($size->isUnknown()) {
return false;
}

assert($size instanceof Known);

return $size->isGreaterThan($other);
}

public function returnValue(string $method): mixed
{
if (isset($this->passedTestMethods[$method])) {
return $this->passedTestMethods[$method]['returnValue'];
}

return null;
}
}
