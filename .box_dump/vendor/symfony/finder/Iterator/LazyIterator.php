<?php










namespace Symfony\Component\Finder\Iterator;






class LazyIterator implements \IteratorAggregate
{
private \Closure $iteratorFactory;

public function __construct(callable $iteratorFactory)
{
$this->iteratorFactory = $iteratorFactory(...);
}

public function getIterator(): \Traversable
{
yield from ($this->iteratorFactory)();
}
}
