<?php declare(strict_types=1);








namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
@psalm-immutable
@no-named-arguments

*/
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class DependsExternalUsingShallowClone
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
