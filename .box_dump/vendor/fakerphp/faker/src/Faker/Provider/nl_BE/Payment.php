<?php

namespace Faker\Provider\nl_BE;

class Payment extends \Faker\Provider\Payment
{











public static function bankAccountNumber($prefix = '', $countryCode = 'BE', $length = null)
{
return static::iban($countryCode, $prefix, $length);
}














public static function vat($spacedNationalPrefix = true)
{
$prefix = $spacedNationalPrefix ? 'BE ' : 'BE';


$firstSeven = self::randomNumber(7, true);


$checksum = 97 - fmod($firstSeven, 97);


return sprintf('%s0%s%02d', $prefix, $firstSeven, $checksum);
}
}
