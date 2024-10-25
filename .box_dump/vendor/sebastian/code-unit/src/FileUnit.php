<?php declare(strict_types=1);








namespace SebastianBergmann\CodeUnit;

/**
@psalm-immutable
*/
final class FileUnit extends CodeUnit
{
/**
@psalm-assert-if-true
*/
public function isFile(): bool
{
return true;
}
}
