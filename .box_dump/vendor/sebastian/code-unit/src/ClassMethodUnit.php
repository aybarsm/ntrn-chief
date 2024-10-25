<?php declare(strict_types=1);








namespace SebastianBergmann\CodeUnit;

/**
@psalm-immutable
*/
final class ClassMethodUnit extends CodeUnit
{
/**
@psalm-assert-if-true
*/
public function isClassMethod(): bool
{
return true;
}
}
