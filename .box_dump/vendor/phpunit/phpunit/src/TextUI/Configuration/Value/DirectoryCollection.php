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
final class DirectoryCollection implements Countable, IteratorAggregate
{
/**
@psalm-var
*/
private readonly array $directories;

/**
@psalm-param
*/
public static function fromArray(array $directories): self
{
return new self(...$directories);
}

private function __construct(Directory ...$directories)
{
$this->directories = $directories;
}

/**
@psalm-return
*/
public function asArray(): array
{
return $this->directories;
}

public function count(): int
{
return count($this->directories);
}

public function getIterator(): DirectoryCollectionIterator
{
return new DirectoryCollectionIterator($this);
}

public function isEmpty(): bool
{
return $this->count() === 0;
}
}
