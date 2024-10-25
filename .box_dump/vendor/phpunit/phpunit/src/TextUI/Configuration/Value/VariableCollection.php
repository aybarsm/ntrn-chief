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
final class VariableCollection implements Countable, IteratorAggregate
{
/**
@psalm-var
*/
private readonly array $variables;

/**
@psalm-param
*/
public static function fromArray(array $variables): self
{
return new self(...$variables);
}

private function __construct(Variable ...$variables)
{
$this->variables = $variables;
}

/**
@psalm-return
*/
public function asArray(): array
{
return $this->variables;
}

public function count(): int
{
return count($this->variables);
}

public function getIterator(): VariableCollectionIterator
{
return new VariableCollectionIterator($this);
}
}
