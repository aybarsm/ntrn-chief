<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class ExcludeStaticPropertyFromBackup extends Metadata
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
@psalm-param
*/
protected function __construct(int $level, string $className, string $propertyName)
{
parent::__construct($level);

$this->className = $className;
$this->propertyName = $propertyName;
}

/**
@psalm-assert-if-true
*/
public function isExcludeStaticPropertyFromBackup(): bool
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
public function propertyName(): string
{
return $this->propertyName;
}
}
