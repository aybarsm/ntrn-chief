<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

use PHPUnit\Metadata\Version\Requirement;

/**
@psalm-immutable
@no-named-arguments

*/
final class RequiresPhpExtension extends Metadata
{
/**
@psalm-var
*/
private readonly string $extension;
private readonly ?Requirement $versionRequirement;

/**
@psalm-param
@psalm-param
*/
protected function __construct(int $level, string $extension, ?Requirement $versionRequirement)
{
parent::__construct($level);

$this->extension = $extension;
$this->versionRequirement = $versionRequirement;
}

/**
@psalm-assert-if-true
*/
public function isRequiresPhpExtension(): bool
{
return true;
}

/**
@psalm-return
*/
public function extension(): string
{
return $this->extension;
}

/**
@psalm-assert-if-true
*/
public function hasVersionRequirement(): bool
{
return $this->versionRequirement !== null;
}




public function versionRequirement(): Requirement
{
if ($this->versionRequirement === null) {
throw new NoVersionRequirementException;
}

return $this->versionRequirement;
}
}
