<?php declare(strict_types=1);








namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
@psalm-immutable
@no-named-arguments

*/
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class ExcludeStaticPropertyFromBackup
{
/**
@psalm-var
*/
private readonly string $className;

/**
@psalm-var
*/
private readonly string $propertyName;

/**
@psalm-param
@psalm-param
*/
public function __construct(string $className, string $propertyName)
{
$this->className = $className;
$this->propertyName = $propertyName;
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
public function propertyName(): string
{
return $this->propertyName;
}
}
