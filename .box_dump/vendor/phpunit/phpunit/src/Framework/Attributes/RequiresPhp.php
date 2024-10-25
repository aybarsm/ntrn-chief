<?php declare(strict_types=1);








namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
@psalm-immutable
@no-named-arguments

*/
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class RequiresPhp
{
/**
@psalm-var
*/
private readonly string $versionRequirement;

/**
@psalm-param
*/
public function __construct(string $versionRequirement)
{
$this->versionRequirement = $versionRequirement;
}

/**
@psalm-return
*/
public function versionRequirement(): string
{
return $this->versionRequirement;
}
}
