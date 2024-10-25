<?php declare(strict_types=1);








namespace PHPUnit\TextUI\Configuration;

use function count;
use Countable;
use IteratorAggregate;

/**
@no-named-arguments
@psalm-immutable
@template-implements


*/
final class TestSuiteCollection implements Countable, IteratorAggregate
{
/**
@psalm-var
*/
private readonly array $testSuites;

/**
@psalm-param
*/
public static function fromArray(array $testSuites): self
{
return new self(...$testSuites);
}

private function __construct(TestSuite ...$testSuites)
{
$this->testSuites = $testSuites;
}

/**
@psalm-return
*/
public function asArray(): array
{
return $this->testSuites;
}

public function count(): int
{
return count($this->testSuites);
}

public function getIterator(): TestSuiteCollectionIterator
{
return new TestSuiteCollectionIterator($this);
}

public function isEmpty(): bool
{
return $this->count() === 0;
}
}
