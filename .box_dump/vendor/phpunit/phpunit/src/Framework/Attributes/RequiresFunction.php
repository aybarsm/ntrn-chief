<?php declare(strict_types=1);








namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
@psalm-immutable
@no-named-arguments

*/
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class RequiresFunction
{
/**
@psalm-var
*/
private readonly string $functionName;

/**
@psalm-param
*/
public function __construct(string $functionName)
{
$this->functionName = $functionName;
}

/**
@psalm-return
*/
public function functionName(): string
{
return $this->functionName;
}
}
