<?php declare(strict_types=1);








namespace PHPUnit\Metadata\Version;

use function version_compare;
use PHPUnit\Util\VersionComparisonOperator;

/**
@psalm-immutable
@no-named-arguments

*/
final class ComparisonRequirement extends Requirement
{
private readonly string $version;
private readonly VersionComparisonOperator $operator;

public function __construct(string $version, VersionComparisonOperator $operator)
{
$this->version = $version;
$this->operator = $operator;
}

public function isSatisfiedBy(string $version): bool
{
return version_compare($version, $this->version, $this->operator->asString());
}

public function asString(): string
{
return $this->operator->asString() . ' ' . $this->version;
}
}
