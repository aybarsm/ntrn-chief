<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class RequiresOperatingSystem extends Metadata
{
/**
@psalm-var
*/
private readonly string $operatingSystem;

/**
@psalm-param
@psalm-param
*/
public function __construct(int $level, string $operatingSystem)
{
parent::__construct($level);

$this->operatingSystem = $operatingSystem;
}

/**
@psalm-assert-if-true
*/
public function isRequiresOperatingSystem(): bool
{
return true;
}

/**
@psalm-return
*/
public function operatingSystem(): string
{
return $this->operatingSystem;
}
}
