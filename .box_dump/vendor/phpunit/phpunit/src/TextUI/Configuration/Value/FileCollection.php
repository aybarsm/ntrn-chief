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
final class FileCollection implements Countable, IteratorAggregate
{
/**
@psalm-var
*/
private readonly array $files;

/**
@psalm-param
*/
public static function fromArray(array $files): self
{
return new self(...$files);
}

private function __construct(File ...$files)
{
$this->files = $files;
}

/**
@psalm-return
*/
public function asArray(): array
{
return $this->files;
}

public function count(): int
{
return count($this->files);
}

public function notEmpty(): bool
{
return !empty($this->files);
}

public function getIterator(): FileCollectionIterator
{
return new FileCollectionIterator($this);
}
}
