<?php

namespace Faker\Provider\ar_SA;

class Payment extends \Faker\Provider\Payment
{











public static function bankAccountNumber($prefix = '', $countryCode = 'SA', $length = null)
{
return static::iban($countryCode, $prefix, $length);
}
}
