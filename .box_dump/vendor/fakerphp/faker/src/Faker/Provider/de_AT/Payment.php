<?php

namespace Faker\Provider\de_AT;

class Payment extends \Faker\Provider\Payment
{













public static function vat($spacedNationalPrefix = true)
{
$prefix = $spacedNationalPrefix ? 'AT U' : 'ATU';

return sprintf('%s%d', $prefix, self::randomNumber(8, true));
}












public static function bankAccountNumber($prefix = '', $countryCode = 'AT', $length = null)
{
return static::iban($countryCode, $prefix, $length);
}
}
