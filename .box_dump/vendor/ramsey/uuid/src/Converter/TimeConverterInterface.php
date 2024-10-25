<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Converter;

use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Type\Time;

/**
@psalm-immutable



*/
interface TimeConverterInterface
{
/**
@psalm-pure













*/
public function calculateTime(string $seconds, string $microseconds): Hexadecimal;

/**
@psalm-pure








*/
public function convertTime(Hexadecimal $uuidTimestamp): Time;
}
