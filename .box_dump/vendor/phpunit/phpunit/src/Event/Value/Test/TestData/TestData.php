<?php declare(strict_types=1);








namespace PHPUnit\Event\TestData;

/**
@psalm-immutable
@no-named-arguments

*/
abstract class TestData
{
private readonly string $data;

protected function __construct(string $data)
{
$this->data = $data;
}

public function data(): string
{
return $this->data;
}

/**
@psalm-assert-if-true
*/
public function isFromDataProvider(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isFromTestDependency(): bool
{
return false;
}
}
