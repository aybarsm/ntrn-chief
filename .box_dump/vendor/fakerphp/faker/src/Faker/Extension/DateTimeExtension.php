<?php

namespace Faker\Extension;

/**
@experimental






*/
interface DateTimeExtension
{












public function dateTime($until = 'now', string $timezone = null): \DateTime;












public function dateTimeAD($until = 'now', string $timezone = null): \DateTime;













public function dateTimeBetween($from = '-30 years', $until = 'now', string $timezone = null): \DateTime;













public function dateTimeInInterval($from = '-30 years', string $interval = '+5 days', string $timezone = null): \DateTime;











public function dateTimeThisWeek($until = 'now', string $timezone = null): \DateTime;











public function dateTimeThisMonth($until = 'now', string $timezone = null): \DateTime;











public function dateTimeThisYear($until = 'now', string $timezone = null): \DateTime;











public function dateTimeThisDecade($until = 'now', string $timezone = null): \DateTime;











public function dateTimeThisCentury($until = 'now', string $timezone = null): \DateTime;









public function date(string $format = 'Y-m-d', $until = 'now'): string;









public function time(string $format = 'H:i:s', $until = 'now'): string;






public function unixTime($until = 'now'): int;






public function iso8601($until = 'now'): string;








public function amPm($until = 'now'): string;








public function dayOfMonth($until = 'now'): string;













public function dayOfWeek($until = 'now'): string;








public function month($until = 'now'): string;











public function monthName($until = 'now'): string;








public function year($until = 'now'): string;






public function century(): string;








public function timezone(string $countryCode = null): string;
}
