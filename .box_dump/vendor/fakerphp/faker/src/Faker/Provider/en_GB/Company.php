<?php

namespace Faker\Provider\en_GB;

class Company extends \Faker\Provider\Company
{
public const VAT_PREFIX = 'GB';
public const VAT_TYPE_DEFAULT = 'vat';
public const VAT_TYPE_BRANCH = 'branch';
public const VAT_TYPE_GOVERNMENT = 'gov';
public const VAT_TYPE_HEALTH_AUTHORITY = 'health';









public static function vat(string $type = null): string
{
switch ($type) {
case static::VAT_TYPE_BRANCH:
return static::generateBranchTraderVatNumber();

case static::VAT_TYPE_GOVERNMENT:
return static::generateGovernmentVatNumber();

case static::VAT_TYPE_HEALTH_AUTHORITY:
return static::generateHealthAuthorityVatNumber();

default:
return static::generateStandardVatNumber();
}
}









private static function generateStandardVatNumber(): string
{
$firstBlock = static::numberBetween(100, 999);
$secondBlock = static::randomNumber(4, true);

return sprintf(
'%s%d %d %d',
static::VAT_PREFIX,
$firstBlock,
$secondBlock,
static::calculateModulus97($firstBlock . $secondBlock),
);
}





private static function generateHealthAuthorityVatNumber(): string
{
return sprintf(
'%sHA%d',
static::VAT_PREFIX,
static::numberBetween(500, 999),
);
}





private static function generateBranchTraderVatNumber(): string
{
return sprintf(
'%s %d',
static::generateStandardVatNumber(),
static::randomNumber(3, true),
);
}





private static function generateGovernmentVatNumber(): string
{
return sprintf(
'%sGD%s',
static::VAT_PREFIX,
str_pad((string) static::numberBetween(0, 499), 3, '0', STR_PAD_LEFT),
);
}






public static function calculateModulus97(string $input, bool $use9755 = true): string
{
$digits = str_split($input);

if (count($digits) !== 7) {
throw new \InvalidArgumentException();
}
$multiplier = 8;
$sum = 0;

foreach ($digits as $digit) {
$sum += (int) $digit * $multiplier;
--$multiplier ;
}

if ($use9755) {
$sum = $sum + 55;
}

while ($sum > 0) {
$sum -= 97;
}
$sum = $sum * -1;

return str_pad((string) $sum, 2, '0', STR_PAD_LEFT);
}
}
