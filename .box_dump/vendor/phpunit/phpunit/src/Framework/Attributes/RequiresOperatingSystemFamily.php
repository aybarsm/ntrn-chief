<?php declare(strict_types=1);








namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
@psalm-immutable
@no-named-arguments

*/
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class RequiresOperatingSystemFamily
{
/**
@psalm-var
*/
private readonly string $operatingSystemFamily;

/**
@psalm-param
*/
public function __construct(string $operatingSystemFamily)
{
$this->operatingSystemFamily = $operatingSystemFamily;
}

/**
@psalm-return
*/
public function operatingSystemFamily(): string
{
return $this->operatingSystemFamily;
}
}
