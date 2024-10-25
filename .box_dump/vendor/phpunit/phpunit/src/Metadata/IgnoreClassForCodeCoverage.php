<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments



*/
final class IgnoreClassForCodeCoverage extends Metadata
{
/**
@psalm-var
*/
private readonly string $className;

/**
@psalm-param
@psalm-param
*/
protected function __construct(int $level, string $className)
{
parent::__construct($level);

$this->className = $className;
}

/**
@psalm-assert-if-true
*/
public function isIgnoreClassForCodeCoverage(): bool
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
}
