<?php declare(strict_types=1);








namespace PHPUnit\Logging\TestDox;

use IteratorAggregate;

/**
@template-implements
@psalm-immutable
@no-named-arguments




*/
final class TestResultCollection implements IteratorAggregate
{
/**
@psalm-var
*/
private readonly array $testResults;

/**
@psalm-param
*/
public static function fromArray(array $testResults): self
{
return new self(...$testResults);
}

private function __construct(TestResult ...$testResults)
{
$this->testResults = $testResults;
}

/**
@psalm-return
*/
public function asArray(): array
{
return $this->testResults;
}

public function getIterator(): TestResultCollectionIterator
{
return new TestResultCollectionIterator($this);
}
}
