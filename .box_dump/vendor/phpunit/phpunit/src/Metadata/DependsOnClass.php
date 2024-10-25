<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class DependsOnClass extends Metadata
{
/**
@psalm-var
*/
private readonly string $className;
private readonly bool $deepClone;
private readonly bool $shallowClone;

/**
@psalm-param
@psalm-param
*/
protected function __construct(int $level, string $className, bool $deepClone, bool $shallowClone)
{
parent::__construct($level);

$this->className = $className;
$this->deepClone = $deepClone;
$this->shallowClone = $shallowClone;
}

/**
@psalm-assert-if-true
*/
public function isDependsOnClass(): bool
{
return true;
}

/**
@psalm-return
*/
public function className(): string
{
return $this->className;
}

public function deepClone(): bool
{
return $this->deepClone;
}

public function shallowClone(): bool
{
return $this->shallowClone;
}
}
