<?php

namespace Faker\Provider\he_IL;

class Payment extends \Faker\Provider\Payment
{











public static function bankAccountNumber($prefix = '', $countryCode = 'IL', $length = null)
{
return static::iban($countryCode, $prefix, $length);
}
}
