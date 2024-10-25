<?php declare(strict_types=1);








namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
@psalm-immutable
@no-named-arguments

*/
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class ExcludeGlobalVariableFromBackup
{
/**
@psalm-var
*/
private readonly string $globalVariableName;

/**
@psalm-param
*/
public function __construct(string $globalVariableName)
{
$this->globalVariableName = $globalVariableName;
}

/**
@psalm-return
*/
public function globalVariableName(): string
{
return $this->globalVariableName;
}
}
