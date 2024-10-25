<?php declare(strict_types=1);








namespace PHPUnit\TextUI\Configuration;

use function count;
use function iterator_count;
use Countable;
use Iterator;

/**
@no-named-arguments
@template-implements

*/
final class TestSuiteCollectionIterator implements Countable, Iterator
{
/**
@psalm-var
*/
private readonly array $testSuites;
private int $position = 0;

public function __construct(TestSuiteCollection $testSuites)
{
$this->testSuites = $testSuites->asArray();
}

public function count(): int
{
return iterator_count($this);
}

public function rewind(): void
{
$this->position = 0;
}

public function valid(): bool
{
return $this->position < count($this->testSuites);
}

public function key(): int
{
return $this->position;
}

public function current(): TestSuite
{
return $this->testSuites[$this->position];
}

public function next(): void
{
$this->position++;
}
}
