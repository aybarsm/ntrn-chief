<?php

namespace Faker\Calculator;

class Iban
{





public static function checksum(string $iban)
{

$checkString = substr($iban, 4) . substr($iban, 0, 2) . '00';


$checkString = preg_replace_callback(
'/[A-Z]/',
static function (array $matches): string {
return (string) self::alphaToNumber($matches[0]);
},
$checkString,
);


$checksum = 98 - self::mod97($checkString);

return str_pad($checksum, 2, '0', STR_PAD_LEFT);
}






public static function alphaToNumber(string $char)
{
return ord($char) - 55;
}








public static function mod97(string $number)
{
$checksum = (int) $number[0];

for ($i = 1, $size = strlen($number); $i < $size; ++$i) {
$checksum = (10 * $checksum + (int) $number[$i]) % 97;
}

return $checksum;
}






public static function isValid(string $iban)
{
return self::checksum($iban) === substr($iban, 2, 2);
}
}
