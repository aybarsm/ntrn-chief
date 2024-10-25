<?php declare(strict_types=1);








namespace PHPUnit\Event\TestData;

/**
@psalm-immutable
@no-named-arguments

*/
final class DataFromTestDependency extends TestData
{
public static function from(string $data): self
{
return new self($data);
}

/**
@psalm-assert-if-true
*/
public function isFromTestDependency(): bool
{
return true;
}
}
