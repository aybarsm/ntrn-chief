<?php declare(strict_types=1);








namespace PHPUnit\Metadata\Version;

use function preg_replace;
use PharIo\Version\Version;
use PharIo\Version\VersionConstraint;

/**
@psalm-immutable
@no-named-arguments

*/
final class ConstraintRequirement extends Requirement
{
private readonly VersionConstraint $constraint;

public function __construct(VersionConstraint $constraint)
{
$this->constraint = $constraint;
}

/**
@psalm-suppress
*/
public function isSatisfiedBy(string $version): bool
{
return $this->constraint->complies(
new Version($this->sanitize($version)),
);
}

/**
@psalm-suppress
*/
public function asString(): string
{
return $this->constraint->asString();
}

private function sanitize(string $version): string
{
return preg_replace(
'/^(\d+\.\d+(?:.\d+)?).*$/',
'$1',
$version,
);
}
}
