<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class UsesFunction extends Metadata
{
/**
@psalm-var
*/
private readonly string $functionName;

/**
@psalm-param
@psalm-param
*/
public function __construct(int $level, string $functionName)
{
parent::__construct($level);

$this->functionName = $functionName;
}

/**
@psalm-assert-if-true
*/
public function isUsesFunction(): bool
{
return true;
}

/**
@psalm-return
*/
public function functionName(): string
{
return $this->functionName;
}




public function asStringForCodeUnitMapper(): string
{
return '::' . $this->functionName;
}
}
