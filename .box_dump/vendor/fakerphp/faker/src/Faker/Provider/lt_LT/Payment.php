<?php

namespace Faker\Provider\lt_LT;

class Payment extends \Faker\Provider\Payment
{











public static function bankAccountNumber($prefix = '', $countryCode = 'LT', $length = null)
{
return static::iban($countryCode, $prefix, $length);
}
}
