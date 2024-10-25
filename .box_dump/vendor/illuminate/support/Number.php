<?php

namespace Illuminate\Support;

use Illuminate\Support\Traits\Macroable;
use NumberFormatter;
use RuntimeException;

class Number
{
use Macroable;






protected static $locale = 'en';






protected static $currency = 'USD';










public static function format(int|float $number, ?int $precision = null, ?int $maxPrecision = null, ?string $locale = null)
{
static::ensureIntlExtensionIsInstalled();

$formatter = new NumberFormatter($locale ?? static::$locale, NumberFormatter::DECIMAL);

if (! is_null($maxPrecision)) {
$formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $maxPrecision);
} elseif (! is_null($precision)) {
$formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $precision);
}

return $formatter->format($number);
}










public static function spell(int|float $number, ?string $locale = null, ?int $after = null, ?int $until = null)
{
static::ensureIntlExtensionIsInstalled();

if (! is_null($after) && $number <= $after) {
return static::format($number, locale: $locale);
}

if (! is_null($until) && $number >= $until) {
return static::format($number, locale: $locale);
}

$formatter = new NumberFormatter($locale ?? static::$locale, NumberFormatter::SPELLOUT);

return $formatter->format($number);
}








public static function ordinal(int|float $number, ?string $locale = null)
{
static::ensureIntlExtensionIsInstalled();

$formatter = new NumberFormatter($locale ?? static::$locale, NumberFormatter::ORDINAL);

return $formatter->format($number);
}










public static function percentage(int|float $number, int $precision = 0, ?int $maxPrecision = null, ?string $locale = null)
{
static::ensureIntlExtensionIsInstalled();

$formatter = new NumberFormatter($locale ?? static::$locale, NumberFormatter::PERCENT);

if (! is_null($maxPrecision)) {
$formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $maxPrecision);
} else {
$formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $precision);
}

return $formatter->format($number / 100);
}









public static function currency(int|float $number, string $in = '', ?string $locale = null)
{
static::ensureIntlExtensionIsInstalled();

$formatter = new NumberFormatter($locale ?? static::$locale, NumberFormatter::CURRENCY);

return $formatter->formatCurrency($number, ! empty($in) ? $in : static::$currency);
}









public static function fileSize(int|float $bytes, int $precision = 0, ?int $maxPrecision = null)
{
$units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

for ($i = 0; ($bytes / 1024) > 0.9 && ($i < count($units) - 1); $i++) {
$bytes /= 1024;
}

return sprintf('%s %s', static::format($bytes, $precision, $maxPrecision), $units[$i]);
}









public static function abbreviate(int|float $number, int $precision = 0, ?int $maxPrecision = null)
{
return static::forHumans($number, $precision, $maxPrecision, abbreviate: true);
}










public static function forHumans(int|float $number, int $precision = 0, ?int $maxPrecision = null, bool $abbreviate = false)
{
return static::summarize($number, $precision, $maxPrecision, $abbreviate ? [
3 => 'K',
6 => 'M',
9 => 'B',
12 => 'T',
15 => 'Q',
] : [
3 => ' thousand',
6 => ' million',
9 => ' billion',
12 => ' trillion',
15 => ' quadrillion',
]);
}










protected static function summarize(int|float $number, int $precision = 0, ?int $maxPrecision = null, array $units = [])
{
if (empty($units)) {
$units = [
3 => 'K',
6 => 'M',
9 => 'B',
12 => 'T',
15 => 'Q',
];
}

switch (true) {
case floatval($number) === 0.0:
return $precision > 0 ? static::format(0, $precision, $maxPrecision) : '0';
case $number < 0:
return sprintf('-%s', static::summarize(abs($number), $precision, $maxPrecision, $units));
case $number >= 1e15:
return sprintf('%s'.end($units), static::summarize($number / 1e15, $precision, $maxPrecision, $units));
}

$numberExponent = floor(log10($number));
$displayExponent = $numberExponent - ($numberExponent % 3);
$number /= pow(10, $displayExponent);

return trim(sprintf('%s%s', static::format($number, $precision, $maxPrecision), $units[$displayExponent] ?? ''));
}









public static function clamp(int|float $number, int|float $min, int|float $max)
{
return min(max($number, $min), $max);
}









public static function pairs(int|float $to, int|float $by, int|float $offset = 1)
{
$output = [];

for ($lower = 0; $lower < $to; $lower += $by) {
$upper = $lower + $by;

if ($upper > $to) {
$upper = $to;
}

$output[] = [$lower + $offset, $upper];
}

return $output;
}







public static function trim(int|float $number)
{
return json_decode(json_encode($number));
}








public static function withLocale(string $locale, callable $callback)
{
$previousLocale = static::$locale;

static::useLocale($locale);

return tap($callback(), fn () => static::useLocale($previousLocale));
}








public static function withCurrency(string $currency, callable $callback)
{
$previousCurrency = static::$currency;

static::useCurrency($currency);

return tap($callback(), fn () => static::useCurrency($previousCurrency));
}







public static function useLocale(string $locale)
{
static::$locale = $locale;
}







public static function useCurrency(string $currency)
{
static::$currency = $currency;
}






public static function defaultLocale()
{
return static::$locale;
}






public static function defaultCurrency()
{
return static::$currency;
}






protected static function ensureIntlExtensionIsInstalled()
{
if (! extension_loaded('intl')) {
$method = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];

throw new RuntimeException('The "intl" PHP extension is required to use the ['.$method.'] method.');
}
}
}
