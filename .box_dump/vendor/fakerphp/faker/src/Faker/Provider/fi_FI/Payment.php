<?php

namespace Faker\Provider\fi_FI;

class Payment extends \Faker\Provider\Payment
{











public static function bankAccountNumber($prefix = '', $countryCode = 'FI', $length = null)
{
return static::iban($countryCode, $prefix, $length);
}
}
