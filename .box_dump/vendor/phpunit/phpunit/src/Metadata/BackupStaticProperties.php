<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class BackupStaticProperties extends Metadata
{
private readonly bool $enabled;

/**
@psalm-param
*/
protected function __construct(int $level, bool $enabled)
{
parent::__construct($level);

$this->enabled = $enabled;
}

/**
@psalm-assert-if-true
*/
public function isBackupStaticProperties(): bool
{
return true;
}

public function enabled(): bool
{
return $this->enabled;
}
}
