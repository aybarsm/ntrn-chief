<?php

namespace Faker\Provider\fr_FR;

class Payment extends \Faker\Provider\Payment
{













public function vat($spacedNationalPrefix = true)
{
$siren = Company::siren(false);
$key = (12 + 3 * ($siren % 97)) % 97;
$pattern = "%s%'.02d%s";

if ($spacedNationalPrefix) {
$siren = trim(chunk_split($siren, 3, ' '));
$pattern = "%s %'.02d %s";
}

return sprintf($pattern, 'FR', $key, $siren);
}












public static function bankAccountNumber($prefix = '', $countryCode = 'FR', $length = null)
{
return static::iban($countryCode, $prefix, $length);
}
}
