<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class TestWith extends Metadata
{
private readonly array $data;

/**
@psalm-param
*/
protected function __construct(int $level, array $data)
{
parent::__construct($level);

$this->data = $data;
}

/**
@psalm-assert-if-true
*/
public function isTestWith(): bool
{
return true;
}

public function data(): array
{
return $this->data;
}
}
