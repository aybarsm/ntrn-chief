<?php

namespace Faker\Calculator;









class Luhn
{



private static function checksum(string $number)
{
$number = (string) $number;
$length = strlen($number);
$sum = 0;

for ($i = $length - 1; $i >= 0; $i -= 2) {
$sum += $number[$i];
}

for ($i = $length - 2; $i >= 0; $i -= 2) {
$sum += array_sum(str_split($number[$i] * 2));
}

return $sum % 10;
}




public static function computeCheckDigit(string $partialNumber)
{
$checkDigit = self::checksum($partialNumber . '0');

if ($checkDigit === 0) {
return '0';
}

return (string) (10 - $checkDigit);
}






public static function isValid(string $number)
{
return self::checksum($number) === 0;
}






public static function generateLuhnNumber(string $partialValue)
{
if (!preg_match('/^\d+$/', $partialValue)) {
throw new \InvalidArgumentException('Argument should be an integer.');
}

return $partialValue . Luhn::computeCheckDigit($partialValue);
}
}
