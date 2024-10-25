<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class RequiresOperatingSystemFamily extends Metadata
{
/**
@psalm-var
*/
private readonly string $operatingSystemFamily;

/**
@psalm-param
@psalm-param
*/
protected function __construct(int $level, string $operatingSystemFamily)
{
parent::__construct($level);

$this->operatingSystemFamily = $operatingSystemFamily;
}

/**
@psalm-assert-if-true
*/
public function isRequiresOperatingSystemFamily(): bool
{
return true;
}

/**
@psalm-return
*/
public function operatingSystemFamily(): string
{
return $this->operatingSystemFamily;
}
}
