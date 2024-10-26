<?php declare(strict_types=1);








namespace PHPUnit\Event\Code;

/**
@psalm-immutable
@no-named-arguments

*/
final class ClassMethod
{
/**
@psalm-var
*/
private readonly string $className;

/**
@psalm-var
*/
private readonly string $methodName;

/**
@psalm-param
@psalm-param
*/
public function __construct(string $className, string $methodName)
{
$this->className = $className;
$this->methodName = $methodName;
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
}
