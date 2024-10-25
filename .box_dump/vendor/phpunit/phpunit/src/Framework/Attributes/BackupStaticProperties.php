<?php declare(strict_types=1);








namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
@psalm-immutable
@no-named-arguments

*/
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class BackupStaticProperties
{
private readonly bool $enabled;

public function __construct(bool $enabled)
{
$this->enabled = $enabled;
}

public function enabled(): bool
{
return $this->enabled;
}
}
