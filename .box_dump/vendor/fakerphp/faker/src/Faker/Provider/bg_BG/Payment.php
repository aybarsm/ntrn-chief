<?php

namespace Faker\Provider\bg_BG;

class Payment extends \Faker\Provider\Payment
{











public static function bankAccountNumber($prefix = '', $countryCode = 'BG', $length = null)
{
return static::iban($countryCode, $prefix, $length);
}













public static function vat($spacedNationalPrefix = true)
{
$prefix = $spacedNationalPrefix ? 'BG ' : 'BG';

return sprintf(
'%s%d%d',
$prefix,
self::randomNumber(5, true), 
self::randomNumber(self::randomElement([4, 5]), true),
);
}
}
