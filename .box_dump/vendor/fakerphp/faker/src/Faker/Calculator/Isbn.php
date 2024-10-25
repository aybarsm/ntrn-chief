<?php

namespace Faker\Calculator;




class Isbn
{



public const PATTERN = '/^\d{9}[0-9X]$/';










public static function checksum(string $input): string
{


$length = 9;

if (strlen($input) !== $length) {
throw new \LengthException(sprintf('Input length should be equal to %d', $length));
}

$digits = str_split($input);
array_walk(
$digits,
static function (&$digit, $position): void {
$digit = (10 - $position) * $digit;
},
);
$result = (11 - array_sum($digits) % 11) % 11;


return ($result < 10) ? (string) $result : 'X';
}






public static function isValid(string $isbn): bool
{
if (!preg_match(self::PATTERN, $isbn)) {
return false;
}

return self::checksum(substr($isbn, 0, -1)) === substr($isbn, -1);
}
}
