<?php declare(strict_types=1);








namespace SebastianBergmann\CodeUnit;

/**
@psalm-immutable
*/
final class TraitUnit extends CodeUnit
{
/**
@psalm-assert-if-true
*/
public function isTrait(): bool
{
return true;
}
}
