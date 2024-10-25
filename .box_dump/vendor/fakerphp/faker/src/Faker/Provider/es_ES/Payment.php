<?php

namespace Faker\Provider\es_ES;

class Payment extends \Faker\Provider\Payment
{
private static $vatMap = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'N', 'P', 'Q', 'R', 'S', 'U', 'V', 'W'];












public static function bankAccountNumber($prefix = '', $countryCode = 'ES', $length = null)
{
return static::iban($countryCode, $prefix, $length);
}











public static function vat()
{
$letter = static::randomElement(self::$vatMap);
$number = static::numerify('########');

return $letter . $number;
}
}
