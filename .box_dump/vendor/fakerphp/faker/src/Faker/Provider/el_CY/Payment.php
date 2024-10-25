<?php

namespace Faker\Provider\el_CY;

class Payment extends \Faker\Provider\Payment
{











public static function bankAccountNumber($prefix = '', $countryCode = 'CY', $length = null)
{
return static::iban($countryCode, $prefix, $length);
}






protected static $banks = [
'Τράπεζα Κύπρου',
'Ελληνική Τράπεζα',
'Alpha Bank Cyprus',
'Εθνική Τράπεζα της Ελλάδος (Κύπρου)',
'USB BANK',
'Κυπριακή Τράπεζα Αναπτύξεως',
'Societe Gererale Cyprus',
'Τράπεζα Πειραιώς (Κύπρου)',
'RCB Bank',
'Eurobank Cyprus',
'Συνεργατική Κεντρική Τράπεζα',
'Ancoria Bank',
];




public static function bank()
{
return static::randomElement(static::$banks);
}
}
