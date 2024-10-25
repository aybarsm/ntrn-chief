<?php

namespace Faker\Provider\en_CA;




class Address extends \Faker\Provider\en_US\Address
{
protected static $postcode = ['?#? #?#', '?#?-#?#', '?#?#?#'];

protected static $postcodeLetters = ['A', 'B', 'C', 'E', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'R', 'S', 'T', 'V', 'X', 'Y'];

protected static $province = [
'Alberta',
'British Columbia',
'Manitoba',
'New Brunswick', 'Newfoundland and Labrador', 'Northwest Territories', 'Nova Scotia', 'Nunavut',
'Ontario',
'Prince Edward Island',
'Quebec',
'Saskatchewan',
'Yukon Territory',
];

protected static $provinceAbbr = [
'AB', 'BC', 'MB', 'NB', 'NL', 'NT', 'NS', 'NU', 'ON', 'PE', 'QC', 'SK', 'YT',
];

protected static $addressFormats = [
"{{streetAddress}}\n{{city}}, {{provinceAbbr}}  {{postcode}}",
];




public static function province()
{
return static::randomElement(static::$province);
}




public static function provinceAbbr()
{
return static::randomElement(static::$provinceAbbr);
}






public static function randomPostcodeLetter()
{
return static::randomElement(static::$postcodeLetters);
}




public static function postcode()
{
$string = static::randomElement(static::$postcode);

$string = preg_replace_callback('/\#/u', [static::class, 'randomDigit'], $string);
$string = preg_replace_callback('/\?/u', [static::class, 'randomPostcodeLetter'], $string);

return static::toUpper($string);
}
}
