<?php declare(strict_types=1);








namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
@psalm-immutable
@no-named-arguments

*/
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class DataProvider
{
/**
@psalm-var
*/
private readonly string $methodName;

/**
@psalm-param
*/
public function __construct(string $methodName)
{
$this->methodName = $methodName;
}

/**
@psalm-return
*/
public function methodName(): string
{
return $this->methodName;
}
}
