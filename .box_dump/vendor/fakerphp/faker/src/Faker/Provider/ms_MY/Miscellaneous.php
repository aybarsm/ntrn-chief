<?php

namespace Faker\Provider\ms_MY;

class Miscellaneous extends \Faker\Provider\Miscellaneous
{



protected static $jpjNumberPlateFormats = [
'{{peninsularPrefix}}{{validAlphabet}}{{validAlphabet}} {{numberSequence}}',
'{{peninsularPrefix}}{{validAlphabet}}{{validAlphabet}} {{numberSequence}}',
'{{peninsularPrefix}}{{validAlphabet}}{{validAlphabet}} {{numberSequence}}',
'{{peninsularPrefix}}{{validAlphabet}}{{validAlphabet}} {{numberSequence}}',
'W{{validAlphabet}}{{validAlphabet}} {{numberSequence}} {{validAlphabet}}',
'KV {{numberSequence}} {{validAlphabet}}',
'{{sarawakPrefix}} {{numberSequence}} {{validAlphabet}}',
'{{sabahPrefix}} {{numberSequence}} {{validAlphabet}}',
'{{specialPrefix}} {{numberSequence}}',
];







protected static $peninsularPrefix = [
'A', 'A', 'B', 'C', 'D', 'F', 'J', 'J', 'K', 'M', 'N', 'P', 'P', 'R', 'T', 'V',
'W', 'W', 'W', 'W', 'W', 'W',
];




protected static $sarawakPrefix = [
'QA', 'QK', 'QB', 'QC', 'QL', 'QM', 'QP', 'QR', 'QS', 'QT',
];




protected static $sabahPrefix = [
'SA', 'SAA', 'SAB', 'SAC', 'SB', 'SD', 'SG',
'SK', 'SL', 'SS', 'SSA', 'ST', 'STA', 'SU',
];




protected static $specialPrefix = [
'1M4U',
'A1M',
'BAMbee',
'Chancellor',
'G', 'G1M', 'GP', 'GT',
'Jaguh',
'K1M', 'KRISS',
'LOTUS',
'NAAM', 'NAZA', 'NBOS',
'PATRIOT', 'Perdana', 'PERFECT', 'Perodua', 'Persona', 'Proton', 'Putra', 'PUTRAJAYA',
'RIMAU',
'SAM', 'SAS', 'Satria', 'SMS', 'SUKOM',
'T1M', 'Tiara', 'TTB',
'U', 'US',
'VIP',
'WAJA',
'XIIINAM', 'XOIC', 'XXVIASEAN', 'XXXIDB',
'Y',
];






protected static $validAlphabets = [
'A', 'B', 'C', 'D', 'E', 'F',
'G', 'H', 'J', 'K', 'L', 'M',
'N', 'P', 'Q', 'R', 'S', 'T',
'U', 'V', 'W', 'X', 'Y', '',
];








public function jpjNumberPlate()
{
$formats = static::toUpper(static::lexify(static::bothify(static::randomElement(static::$jpjNumberPlateFormats))));

return $this->generator->parse($formats);
}








public static function peninsularPrefix()
{
return static::randomElement(static::$peninsularPrefix);
}








public static function sarawakPrefix()
{
return static::randomElement(static::$sarawakPrefix);
}








public static function sabahPrefix()
{
return static::randomElement(static::$sabahPrefix);
}








public static function specialPrefix()
{
return static::randomElement(static::$specialPrefix);
}








public static function validAlphabet()
{
return static::randomElement(static::$validAlphabets);
}








public static function numberSequence()
{
return self::numberBetween(1, 9999);
}
}
