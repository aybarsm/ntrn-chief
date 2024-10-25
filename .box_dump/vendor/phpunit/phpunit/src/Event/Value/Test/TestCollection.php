<?php declare(strict_types=1);








namespace PHPUnit\Event\Code;

use function count;
use Countable;
use IteratorAggregate;

/**
@template-implements
@psalm-immutable
@no-named-arguments


*/
final class TestCollection implements Countable, IteratorAggregate
{
/**
@psalm-var
*/
private readonly array $tests;

/**
@psalm-param
*/
public static function fromArray(array $tests): self
{
return new self(...$tests);
}

private function __construct(Test ...$tests)
{
$this->tests = $tests;
}

/**
@psalm-return
*/
public function asArray(): array
{
return $this->tests;
}

public function count(): int
{
return count($this->tests);
}

public function getIterator(): TestCollectionIterator
{
return new TestCollectionIterator($this);
}
}
