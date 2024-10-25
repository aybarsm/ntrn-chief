<?php declare(strict_types=1);








namespace PHPUnit\TextUI\Configuration;

use PHPUnit\Util\VersionComparisonOperator;

/**
@no-named-arguments
@psalm-immutable

*/
final class TestDirectory
{
/**
@psalm-var
*/
private readonly string $path;
private readonly string $prefix;
private readonly string $suffix;
private readonly string $phpVersion;
private readonly VersionComparisonOperator $phpVersionOperator;

/**
@psalm-param
*/
public function __construct(string $path, string $prefix, string $suffix, string $phpVersion, VersionComparisonOperator $phpVersionOperator)
{
$this->path = $path;
$this->prefix = $prefix;
$this->suffix = $suffix;
$this->phpVersion = $phpVersion;
$this->phpVersionOperator = $phpVersionOperator;
}

/**
@psalm-return
*/
public function path(): string
{
return $this->path;
}

public function prefix(): string
{
return $this->prefix;
}

public function suffix(): string
{
return $this->suffix;
}

public function phpVersion(): string
{
return $this->phpVersion;
}

public function phpVersionOperator(): VersionComparisonOperator
{
return $this->phpVersionOperator;
}
}
