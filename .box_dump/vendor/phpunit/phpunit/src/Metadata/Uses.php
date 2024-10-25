<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class Uses extends Metadata
{
/**
@psalm-var
*/
private readonly string $target;

/**
@psalm-param
@psalm-param
*/
protected function __construct(int $level, string $target)
{
parent::__construct($level);

$this->target = $target;
}

/**
@psalm-assert-if-true
*/
public function isUses(): bool
{
return true;
}

/**
@psalm-return
*/
public function target(): string
{
return $this->target;
}
}
