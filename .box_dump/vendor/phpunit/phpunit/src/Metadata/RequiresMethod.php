<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class RequiresMethod extends Metadata
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
@psalm-param
*/
protected function __construct(int $level, string $className, string $methodName)
{
parent::__construct($level);

$this->className = $className;
$this->methodName = $methodName;
}

/**
@psalm-assert-if-true
*/
public function isRequiresMethod(): bool
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
}
