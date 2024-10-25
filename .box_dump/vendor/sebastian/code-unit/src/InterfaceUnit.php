<?php declare(strict_types=1);








namespace SebastianBergmann\CodeUnit;

/**
@psalm-immutable
*/
final class InterfaceUnit extends CodeUnit
{
/**
@psalm-assert-if-true
*/
public function isInterface(): bool
{
return true;
}
}
