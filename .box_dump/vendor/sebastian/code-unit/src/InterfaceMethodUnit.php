<?php declare(strict_types=1);








namespace SebastianBergmann\CodeUnit;

/**
@psalm-immutable
*/
final class InterfaceMethodUnit extends CodeUnit
{
/**
@psalm-assert-if-true
*/
public function isInterfaceMethod(): bool
{
return true;
}
}
