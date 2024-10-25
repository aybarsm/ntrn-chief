<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

use PHPUnit\Metadata\Version\Requirement;

/**
@psalm-immutable
@no-named-arguments

*/
final class RequiresPhp extends Metadata
{
private readonly Requirement $versionRequirement;

/**
@psalm-param
*/
protected function __construct(int $level, Requirement $versionRequirement)
{
parent::__construct($level);

$this->versionRequirement = $versionRequirement;
}

/**
@psalm-assert-if-true
*/
public function isRequiresPhp(): bool
{
return true;
}

public function versionRequirement(): Requirement
{
return $this->versionRequirement;
}
}
