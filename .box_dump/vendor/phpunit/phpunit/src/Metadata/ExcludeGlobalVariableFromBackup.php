<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class ExcludeGlobalVariableFromBackup extends Metadata
{
/**
@psalm-var
*/
private readonly string $globalVariableName;

/**
@psalm-param
@psalm-param
*/
protected function __construct(int $level, string $globalVariableName)
{
parent::__construct($level);

$this->globalVariableName = $globalVariableName;
}

/**
@psalm-assert-if-true
*/
public function isExcludeGlobalVariableFromBackup(): bool
{
return true;
}

/**
@psalm-return
*/
public function globalVariableName(): string
{
return $this->globalVariableName;
}
}
