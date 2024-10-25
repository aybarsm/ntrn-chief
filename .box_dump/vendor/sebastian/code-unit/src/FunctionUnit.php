<?php declare(strict_types=1);








namespace SebastianBergmann\CodeUnit;

/**
@psalm-immutable
*/
final class FunctionUnit extends CodeUnit
{
/**
@psalm-assert-if-true
*/
public function isFunction(): bool
{
return true;
}
}
