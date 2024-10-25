<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class Group extends Metadata
{
/**
@psalm-var
*/
private readonly string $groupName;

/**
@psalm-param
@psalm-param
*/
protected function __construct(int $level, string $groupName)
{
parent::__construct($level);

$this->groupName = $groupName;
}

/**
@psalm-assert-if-true
*/
public function isGroup(): bool
{
return true;
}

/**
@psalm-return
*/
public function groupName(): string
{
return $this->groupName;
}
}
