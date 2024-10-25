<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class DependsOnMethod extends Metadata
{
/**
@psalm-var
*/
private readonly string $className;

/**
@psalm-var
*/
private readonly string $methodName;
private readonly bool $deepClone;
private readonly bool $shallowClone;

/**
@psalm-param
@psalm-param
@psalm-param
*/
protected function __construct(int $level, string $className, string $methodName, bool $deepClone, bool $shallowClone)
{
parent::__construct($level);

$this->className = $className;
$this->methodName = $methodName;
$this->deepClone = $deepClone;
$this->shallowClone = $shallowClone;
}

/**
@psalm-assert-if-true
*/
public function isDependsOnMethod(): bool
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

/**
@psalm-return
*/
public function methodName(): string
{
return $this->methodName;
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
