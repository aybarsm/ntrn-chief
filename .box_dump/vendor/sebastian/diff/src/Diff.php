<?php declare(strict_types=1);








namespace SebastianBergmann\Diff;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
@template-implements
*/
final class Diff implements IteratorAggregate
{
/**
@psalm-var
*/
private string $from;

/**
@psalm-var
*/
private string $to;

/**
@psalm-var
*/
private array $chunks;

/**
@psalm-param
@psalm-param
@psalm-param
*/
public function __construct(string $from, string $to, array $chunks = [])
{
$this->from = $from;
$this->to = $to;
$this->chunks = $chunks;
}

/**
@psalm-return
*/
public function from(): string
{
return $this->from;
}

/**
@psalm-return
*/
public function to(): string
{
return $this->to;
}

/**
@psalm-return
*/
public function chunks(): array
{
return $this->chunks;
}

/**
@psalm-param
*/
public function setChunks(array $chunks): void
{
$this->chunks = $chunks;
}

/**
@psalm-return


*/
public function getFrom(): string
{
return $this->from;
}

/**
@psalm-return


*/
public function getTo(): string
{
return $this->to;
}

/**
@psalm-return


*/
public function getChunks(): array
{
return $this->chunks;
}

public function getIterator(): Traversable
{
return new ArrayIterator($this->chunks);
}
}
