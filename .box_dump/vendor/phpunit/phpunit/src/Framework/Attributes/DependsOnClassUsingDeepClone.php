<?php declare(strict_types=1);








namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
@psalm-immutable
@no-named-arguments

*/
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class DependsOnClassUsingDeepClone
{
/**
@psalm-var
*/
private readonly string $className;

/**
@psalm-param
*/
public function __construct(string $className)
{
$this->className = $className;
}

/**
@psalm-return
*/
public function className(): string
{
return $this->className;
}
}
