<?php declare(strict_types=1);








namespace SebastianBergmann\CodeUnit;

/**
@psalm-immutable
*/
final class TraitMethodUnit extends CodeUnit
{
/**
@psalm-assert-if-true
*/
public function isTraitMethod(): bool
{
return true;
}
}
