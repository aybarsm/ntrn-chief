<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Converter;

/**
@psalm-immutable



*/
interface NumberConverterInterface
{
/**
@psalm-return
@psalm-pure











*/
public function fromHex(string $hex): string;

/**
@psalm-return
@psalm-pure










*/
public function toHex(string $number): string;
}
