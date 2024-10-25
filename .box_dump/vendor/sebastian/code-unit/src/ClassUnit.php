<?php declare(strict_types=1);








namespace SebastianBergmann\CodeUnit;

/**
@psalm-immutable
*/
final class ClassUnit extends CodeUnit
{
/**
@psalm-assert-if-true
*/
public function isClass(): bool
{
return true;
}
}
