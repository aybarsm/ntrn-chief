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
final class ConstantCollection implements Countable, IteratorAggregate
{
/**
@psalm-var
*/
private readonly array $constants;

/**
@psalm-param
*/
public static function fromArray(array $constants): self
{
return new self(...$constants);
}

private function __construct(Constant ...$constants)
{
$this->constants = $constants;
}

/**
@psalm-return
*/
public function asArray(): array
{
return $this->constants;
}

public function count(): int
{
return count($this->constants);
}

public function getIterator(): ConstantCollectionIterator
{
return new ConstantCollectionIterator($this);
}
}
