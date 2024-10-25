<?php

namespace Faker\Provider\fi_FI;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{





protected static $landLineareaCodes = [
'02',
'03',
'05',
'06',
'08',
'09',
'013',
'014',
'015',
'016',
'017',
'018',
'019',
];






protected static $mobileNetworkAreaCodes = [
'040',
'050',
'044',
'045',
];

protected static $numberFormats = [
'### ####',
'#######',
];

protected static $formats = [
'+358 ({{ e164MobileNetworkAreaCode }}) {{ numberFormat }}',
'+358 {{ e164MobileNetworkAreaCode }} {{ numberFormat }}',
'+358 ({{ e164landLineAreaCode }}) {{ numberFormat }}',
'+358 {{ e164landLineAreaCode }} {{ numberFormat }}',
'{{ mobileNetworkAreaCode }}{{ separator }}{{ numberFormat }}',
'{{ landLineAreaCode }}{{ separator }}{{ numberFormat }}',
];




public function landLineAreaCode()
{
return static::randomElement(static::$landLineareaCodes);
}




public function e164landLineAreaCode()
{
return substr(static::randomElement(static::$landLineareaCodes), 1);
}




public function mobileNetworkAreaCode()
{
return static::randomElement(static::$mobileNetworkAreaCodes);
}




public function e164MobileNetworkAreaCode()
{
return substr(static::randomElement(static::$mobileNetworkAreaCodes), 1);
}




public function numberFormat()
{
return static::randomElement(static::$numberFormats);
}




public function separator()
{
return static::randomElement([' ', '-']);
}
}
