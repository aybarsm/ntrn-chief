<?php

namespace Faker\Provider;

class DateTime extends Base
{
protected static $century = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'XIII', 'XIV', 'XV', 'XVI', 'XVII', 'XVIII', 'XIX', 'XX', 'XXI'];

protected static $defaultTimezone = null;






protected static function getMaxTimestamp($max = 'now')
{
if (is_numeric($max)) {
return (int) $max;
}

if ($max instanceof \DateTime) {
return $max->getTimestamp();
}

return strtotime(empty($max) ? 'now' : $max);
}










public static function unixTime($max = 'now')
{
return self::numberBetween(0, static::getMaxTimestamp($max));
}














public static function dateTime($max = 'now', $timezone = null)
{
return static::setTimezone(
new \DateTime('@' . static::unixTime($max)),
$timezone,
);
}














public static function dateTimeAD($max = 'now', $timezone = null)
{
$min = (PHP_INT_SIZE > 4 ? -62135597361 : -PHP_INT_MAX);

return static::setTimezone(
new \DateTime('@' . self::numberBetween($min, static::getMaxTimestamp($max))),
$timezone,
);
}










public static function iso8601($max = 'now')
{
return static::date(\DateTime::ISO8601, $max);
}











public static function date($format = 'Y-m-d', $max = 'now')
{
return static::dateTime($max)->format($format);
}











public static function time($format = 'H:i:s', $max = 'now')
{
return static::dateTime($max)->format($format);
}
















public static function dateTimeBetween($startDate = '-30 years', $endDate = 'now', $timezone = null)
{
$startTimestamp = $startDate instanceof \DateTime ? $startDate->getTimestamp() : strtotime($startDate);
$endTimestamp = static::getMaxTimestamp($endDate);

if ($startTimestamp > $endTimestamp) {
throw new \InvalidArgumentException('Start date must be anterior to end date.');
}

$timestamp = self::numberBetween($startTimestamp, $endTimestamp);

return static::setTimezone(
new \DateTime('@' . $timestamp),
$timezone,
);
}

















public static function dateTimeInInterval($date = '-30 years', $interval = '+5 days', $timezone = null)
{
$intervalObject = \DateInterval::createFromDateString($interval);
$datetime = $date instanceof \DateTime ? $date : new \DateTime($date);
$otherDatetime = clone $datetime;
$otherDatetime->add($intervalObject);

$begin = min($datetime, $otherDatetime);
$end = $datetime === $begin ? $otherDatetime : $datetime;

return static::dateTimeBetween(
$begin,
$end,
$timezone,
);
}









public static function dateTimeThisCentury($max = 'now', $timezone = null)
{
return static::dateTimeBetween('-100 year', $max, $timezone);
}









public static function dateTimeThisDecade($max = 'now', $timezone = null)
{
return static::dateTimeBetween('-10 year', $max, $timezone);
}









public static function dateTimeThisYear($max = 'now', $timezone = null)
{
return static::dateTimeBetween('first day of january this year', $max, $timezone);
}









public static function dateTimeThisMonth($max = 'now', $timezone = null)
{
return static::dateTimeBetween('-1 month', $max, $timezone);
}










public static function amPm($max = 'now')
{
return static::dateTime($max)->format('a');
}








public static function dayOfMonth($max = 'now')
{
return static::dateTime($max)->format('d');
}








public static function dayOfWeek($max = 'now')
{
return static::dateTime($max)->format('l');
}








public static function month($max = 'now')
{
return static::dateTime($max)->format('m');
}








public static function monthName($max = 'now')
{
return static::dateTime($max)->format('F');
}








public static function year($max = 'now')
{
return static::dateTime($max)->format('Y');
}






public static function century()
{
return static::randomElement(static::$century);
}






public static function timezone(string $countryCode = null)
{
if ($countryCode) {
$timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $countryCode);
} else {
$timezones = \DateTimeZone::listIdentifiers();
}

return static::randomElement($timezones);
}








private static function setTimezone(\DateTime $dt, $timezone)
{
return $dt->setTimezone(new \DateTimeZone(static::resolveTimezone($timezone)));
}






public static function setDefaultTimezone($timezone = null)
{
static::$defaultTimezone = $timezone;
}






public static function getDefaultTimezone()
{
return static::$defaultTimezone;
}






private static function resolveTimezone($timezone)
{
return (null === $timezone) ? ((null === static::$defaultTimezone) ? date_default_timezone_get() : static::$defaultTimezone) : $timezone;
}
}
