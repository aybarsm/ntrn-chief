<?php










namespace Symfony\Component\Finder\Iterator;

/**
@extends







*/
class CustomFilterIterator extends \FilterIterator
{
private array $filters = [];







public function __construct(\Iterator $iterator, array $filters)
{
foreach ($filters as $filter) {
if (!\is_callable($filter)) {
throw new \InvalidArgumentException('Invalid PHP callback.');
}
}
$this->filters = $filters;

parent::__construct($iterator);
}




public function accept(): bool
{
$fileinfo = $this->current();

foreach ($this->filters as $filter) {
if (false === $filter($fileinfo)) {
return false;
}
}

return true;
}
}
