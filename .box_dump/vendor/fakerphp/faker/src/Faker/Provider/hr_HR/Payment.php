<?php

namespace Faker\Provider\hr_HR;

class Payment extends \Faker\Provider\Payment
{











public static function bankAccountNumber($prefix = '', $countryCode = 'HR', $length = null)
{
return static::iban($countryCode, $prefix, $length);
}
}
