<?php declare(strict_types=1);








namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
@psalm-immutable
@no-named-arguments

*/
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class RequiresPhpExtension
{
/**
@psalm-var
*/
private readonly string $extension;

/**
@psalm-var
*/
private readonly ?string $versionRequirement;

/**
@psalm-param
@psalm-param
*/
public function __construct(string $extension, ?string $versionRequirement = null)
{
$this->extension = $extension;
$this->versionRequirement = $versionRequirement;
}

/**
@psalm-return
*/
public function extension(): string
{
return $this->extension;
}

/**
@psalm-return
*/
public function versionRequirement(): ?string
{
return $this->versionRequirement;
}
}
