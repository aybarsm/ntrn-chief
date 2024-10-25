<?php

namespace Faker\Calculator;




class Ean
{



public const PATTERN = '/^(?:\d{8}|\d{13})$/';








public static function checksum(string $digits)
{
$sequence = (strlen($digits) + 1) === 8 ? [3, 1] : [1, 3];
$sums = 0;

foreach (str_split($digits) as $n => $digit) {
$sums += ((int) $digit) * $sequence[$n % 2];
}

return (10 - $sums % 10) % 10;
}









public static function isValid(string $ean)
{
if (!preg_match(self::PATTERN, $ean)) {
return false;
}

return self::checksum(substr($ean, 0, -1)) === (int) substr($ean, -1);
}
}
