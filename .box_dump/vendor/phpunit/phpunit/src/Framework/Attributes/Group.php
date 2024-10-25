<?php declare(strict_types=1);








namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
@psalm-immutable
@no-named-arguments

*/
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Group
{
/**
@psalm-var
*/
private readonly string $name;

/**
@psalm-param
*/
public function __construct(string $name)
{
$this->name = $name;
}

/**
@psalm-return
*/
public function name(): string
{
return $this->name;
}
}
