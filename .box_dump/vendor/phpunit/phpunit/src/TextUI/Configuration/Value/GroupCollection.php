<?php declare(strict_types=1);








namespace PHPUnit\TextUI\Configuration;

use IteratorAggregate;

/**
@no-named-arguments
@psalm-immutable
@template-implements


*/
final class GroupCollection implements IteratorAggregate
{
/**
@psalm-var
*/
private readonly array $groups;

/**
@psalm-param
*/
public static function fromArray(array $groups): self
{
return new self(...$groups);
}

private function __construct(Group ...$groups)
{
$this->groups = $groups;
}

/**
@psalm-return
*/
public function asArray(): array
{
return $this->groups;
}

/**
@psalm-return
*/
public function asArrayOfStrings(): array
{
$result = [];

foreach ($this->groups as $group) {
$result[] = $group->name();
}

return $result;
}

public function isEmpty(): bool
{
return empty($this->groups);
}

public function getIterator(): GroupCollectionIterator
{
return new GroupCollectionIterator($this);
}
}
