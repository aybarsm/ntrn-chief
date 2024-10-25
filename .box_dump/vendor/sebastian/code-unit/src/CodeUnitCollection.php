<?php declare(strict_types=1);








namespace SebastianBergmann\CodeUnit;

use function array_merge;
use function count;
use Countable;
use IteratorAggregate;

/**
@template-implements
@psalm-immutable

*/
final class CodeUnitCollection implements Countable, IteratorAggregate
{
/**
@psalm-var
*/
private readonly array $codeUnits;

public static function fromList(CodeUnit ...$codeUnits): self
{
return new self($codeUnits);
}

/**
@psalm-param
*/
private function __construct(array $codeUnits)
{
$this->codeUnits = $codeUnits;
}

/**
@psalm-return
*/
public function asArray(): array
{
return $this->codeUnits;
}

public function getIterator(): CodeUnitCollectionIterator
{
return new CodeUnitCollectionIterator($this);
}

public function count(): int
{
return count($this->codeUnits);
}

public function isEmpty(): bool
{
return empty($this->codeUnits);
}

public function mergeWith(self $other): self
{
return new self(
array_merge(
$this->asArray(),
$other->asArray()
)
);
}
}
