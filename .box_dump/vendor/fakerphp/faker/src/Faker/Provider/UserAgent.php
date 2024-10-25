<?php

namespace Faker\Provider;

class UserAgent extends Base
{
protected static $userAgents = ['firefox', 'chrome', 'internetExplorer', 'opera', 'safari', 'msedge'];

protected static $windowsPlatformTokens = [
'Windows NT 6.2', 'Windows NT 6.1', 'Windows NT 6.0', 'Windows NT 5.2', 'Windows NT 5.1',
'Windows NT 5.01', 'Windows NT 5.0', 'Windows NT 4.0', 'Windows 98; Win 9x 4.90', 'Windows 98',
'Windows 95', 'Windows CE',
];




protected static $linuxProcessor = ['i686', 'x86_64'];




protected static $macProcessor = ['Intel', 'PPC', 'U; Intel', 'U; PPC'];




protected static $lang = ['en-US', 'sl-SI', 'nl-NL'];






public static function macProcessor()
{
return static::randomElement(static::$macProcessor);
}






public static function linuxProcessor()
{
return static::randomElement(static::$linuxProcessor);
}








public static function userAgent()
{
$userAgentName = static::randomElement(static::$userAgents);

return static::$userAgentName();
}








public static function chrome()
{
$saf = self::numberBetween(531, 536) . self::numberBetween(0, 2);

$platforms = [
'(' . static::linuxPlatformToken() . ") AppleWebKit/$saf (KHTML, like Gecko) Chrome/" . self::numberBetween(36, 40) . '.0.' . self::numberBetween(800, 899) . ".0 Mobile Safari/$saf",
'(' . static::windowsPlatformToken() . ") AppleWebKit/$saf (KHTML, like Gecko) Chrome/" . self::numberBetween(36, 40) . '.0.' . self::numberBetween(800, 899) . ".0 Mobile Safari/$saf",
'(' . static::macPlatformToken() . ") AppleWebKit/$saf (KHTML, like Gecko) Chrome/" . self::numberBetween(36, 40) . '.0.' . self::numberBetween(800, 899) . ".0 Mobile Safari/$saf",
];

return 'Mozilla/5.0 ' . static::randomElement($platforms);
}








public static function msedge()
{
$saf = self::numberBetween(531, 537) . '.' . self::numberBetween(0, 2);
$chrv = self::numberBetween(79, 99) . '.0';

$platforms = [
'(' . static::windowsPlatformToken() . ") AppleWebKit/$saf (KHTML, like Gecko) Chrome/$chrv" . '.' . self::numberBetween(4000, 4844) . '.' . self::numberBetween(10, 99) . " Safari/$saf Edg/$chrv" . self::numberBetween(1000, 1146) . '.' . self::numberBetween(0, 99),
'(' . static::macPlatformToken() . ") AppleWebKit/$saf (KHTML, like Gecko) Chrome/$chrv" . '.' . self::numberBetween(4000, 4844) . '.' . self::numberBetween(10, 99) . " Safari/$saf Edg/$chrv" . self::numberBetween(1000, 1146) . '.' . self::numberBetween(0, 99),
'(' . static::linuxPlatformToken() . ") AppleWebKit/$saf (KHTML, like Gecko) Chrome/$chrv" . '.' . self::numberBetween(4000, 4844) . '.' . self::numberBetween(10, 99) . " Safari/$saf EdgA/$chrv" . self::numberBetween(1000, 1146) . '.' . self::numberBetween(0, 99),
'(' . static::iosMobileToken() . ") AppleWebKit/$saf (KHTML, like Gecko) Version/15.0 EdgiOS/$chrv" . self::numberBetween(1000, 1146) . '.' . self::numberBetween(0, 99) . " Mobile/15E148 Safari/$saf",
];

return 'Mozilla/5.0 ' . static::randomElement($platforms);
}








public static function firefox()
{
$ver = 'Gecko/' . date('Ymd', self::numberBetween(strtotime('2010-1-1'), time())) . ' Firefox/' . self::numberBetween(35, 37) . '.0';

$platforms = [
'(' . static::windowsPlatformToken() . '; ' . static::randomElement(static::$lang) . '; rv:1.9.' . self::numberBetween(0, 2) . '.20) ' . $ver,
'(' . static::linuxPlatformToken() . '; rv:' . self::numberBetween(5, 7) . '.0) ' . $ver,
'(' . static::macPlatformToken() . ' rv:' . self::numberBetween(2, 6) . '.0) ' . $ver,
];

return 'Mozilla/5.0 ' . static::randomElement($platforms);
}








public static function safari()
{
$saf = self::numberBetween(531, 535) . '.' . self::numberBetween(1, 50) . '.' . self::numberBetween(1, 7);

if (Miscellaneous::boolean()) {
$ver = self::numberBetween(4, 5) . '.' . self::numberBetween(0, 1);
} else {
$ver = self::numberBetween(4, 5) . '.0.' . self::numberBetween(1, 5);
}

$mobileDevices = [
'iPhone; CPU iPhone OS',
'iPad; CPU OS',
];

$platforms = [
'(Windows; U; ' . static::windowsPlatformToken() . ") AppleWebKit/$saf (KHTML, like Gecko) Version/$ver Safari/$saf",
'(' . static::macPlatformToken() . ' rv:' . self::numberBetween(2, 6) . '.0; ' . static::randomElement(static::$lang) . ") AppleWebKit/$saf (KHTML, like Gecko) Version/$ver Safari/$saf",
'(' . static::randomElement($mobileDevices) . ' ' . self::numberBetween(7, 8) . '_' . self::numberBetween(0, 2) . '_' . self::numberBetween(1, 2) . ' like Mac OS X; ' . static::randomElement(static::$lang) . ") AppleWebKit/$saf (KHTML, like Gecko) Version/" . self::numberBetween(3, 4) . '.0.5 Mobile/8B' . self::numberBetween(111, 119) . " Safari/6$saf",
];

return 'Mozilla/5.0 ' . static::randomElement($platforms);
}








public static function opera()
{
$platforms = [
'(' . static::linuxPlatformToken() . '; ' . static::randomElement(static::$lang) . ') Presto/2.' . self::numberBetween(8, 12) . '.' . self::numberBetween(160, 355) . ' Version/' . self::numberBetween(10, 12) . '.00',
'(' . static::windowsPlatformToken() . '; ' . static::randomElement(static::$lang) . ') Presto/2.' . self::numberBetween(8, 12) . '.' . self::numberBetween(160, 355) . ' Version/' . self::numberBetween(10, 12) . '.00',
];

return 'Opera/' . self::numberBetween(8, 9) . '.' . self::numberBetween(10, 99) . ' ' . static::randomElement($platforms);
}








public static function internetExplorer()
{
return 'Mozilla/5.0 (compatible; MSIE ' . self::numberBetween(5, 11) . '.0; ' . static::windowsPlatformToken() . '; Trident/' . self::numberBetween(3, 5) . '.' . self::numberBetween(0, 1) . ')';
}




public static function windowsPlatformToken()
{
return static::randomElement(static::$windowsPlatformTokens);
}




public static function macPlatformToken()
{
return 'Macintosh; ' . static::randomElement(static::$macProcessor) . ' Mac OS X 10_' . self::numberBetween(5, 8) . '_' . self::numberBetween(0, 9);
}




public static function iosMobileToken()
{
$iosVer = self::numberBetween(13, 15) . '_' . self::numberBetween(0, 2);

return 'iPhone; CPU iPhone OS ' . $iosVer . ' like Mac OS X';
}




public static function linuxPlatformToken()
{
return 'X11; Linux ' . static::randomElement(static::$linuxProcessor);
}
}
