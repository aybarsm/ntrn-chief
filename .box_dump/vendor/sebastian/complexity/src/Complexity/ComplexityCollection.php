<?php declare(strict_types=1);








namespace SebastianBergmann\Complexity;

use function array_filter;
use function array_merge;
use function array_reverse;
use function array_values;
use function count;
use function usort;
use Countable;
use IteratorAggregate;

/**
@psalm-immutable
*/
final class ComplexityCollection implements Countable, IteratorAggregate
{
/**
@psalm-var
*/
private readonly array $items;

public static function fromList(Complexity ...$items): self
{
return new self($items);
}

/**
@psalm-param
*/
private function __construct(array $items)
{
$this->items = $items;
}

/**
@psalm-return
*/
public function asArray(): array
{
return $this->items;
}

public function getIterator(): ComplexityCollectionIterator
{
return new ComplexityCollectionIterator($this);
}

/**
@psalm-return
*/
public function count(): int
{
return count($this->items);
}

public function isEmpty(): bool
{
return empty($this->items);
}

/**
@psalm-return
*/
public function cyclomaticComplexity(): int
{
$cyclomaticComplexity = 0;

foreach ($this as $item) {
$cyclomaticComplexity += $item->cyclomaticComplexity();
}

return $cyclomaticComplexity;
}

public function isFunction(): self
{
return new self(
array_values(
array_filter(
$this->items,
static fn (Complexity $complexity): bool => $complexity->isFunction(),
),
),
);
}

public function isMethod(): self
{
return new self(
array_values(
array_filter(
$this->items,
static fn (Complexity $complexity): bool => $complexity->isMethod(),
),
),
);
}

public function mergeWith(self $other): self
{
return new self(
array_merge(
$this->asArray(),
$other->asArray(),
),
);
}

public function sortByDescendingCyclomaticComplexity(): self
{
$items = $this->items;

usort(
$items,
static function (Complexity $a, Complexity $b): int
{
return $a->cyclomaticComplexity() <=> $b->cyclomaticComplexity();
},
);

return new self(array_reverse($items));
}
}
