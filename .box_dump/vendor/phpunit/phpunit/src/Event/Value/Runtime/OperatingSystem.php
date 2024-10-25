<?php declare(strict_types=1);








namespace PHPUnit\Event\Runtime;

use const PHP_OS;
use const PHP_OS_FAMILY;

/**
@psalm-immutable
@no-named-arguments

*/
final class OperatingSystem
{
private readonly string $operatingSystem;
private readonly string $operatingSystemFamily;

public function __construct()
{
$this->operatingSystem = PHP_OS;
$this->operatingSystemFamily = PHP_OS_FAMILY;
}

public function operatingSystem(): string
{
return $this->operatingSystem;
}

public function operatingSystemFamily(): string
{
return $this->operatingSystemFamily;
}
}
