<?php

namespace Faker\Provider;




class Image extends Base
{



public const BASE_URL = 'https://via.placeholder.com';

public const FORMAT_JPG = 'jpg';
public const FORMAT_JPEG = 'jpeg';
public const FORMAT_PNG = 'png';






protected static $categories = [
'abstract', 'animals', 'business', 'cats', 'city', 'food', 'nightlife',
'fashion', 'people', 'nature', 'sports', 'technics', 'transport',
];


















public static function imageUrl(
$width = 640,
$height = 480,
$category = null,
$randomize = true,
$word = null,
$gray = false,
$format = 'png'
) {
trigger_deprecation(
'fakerphp/faker',
'1.20',
'Provider is deprecated and will no longer be available in Faker 2. Please use a custom provider instead',
);


$imageFormats = static::getFormats();

if (!in_array(strtolower($format), $imageFormats, true)) {
throw new \InvalidArgumentException(sprintf(
'Invalid image format "%s". Allowable formats are: %s',
$format,
implode(', ', $imageFormats),
));
}

$size = sprintf('%dx%d.%s', $width, $height, $format);

$imageParts = [];

if ($category !== null) {
$imageParts[] = $category;
}

if ($word !== null) {
$imageParts[] = $word;
}

if ($randomize === true) {
$imageParts[] = Lorem::word();
}

$backgroundColor = $gray === true ? 'CCCCCC' : str_replace('#', '', Color::safeHexColor());

return sprintf(
'%s/%s/%s%s',
self::BASE_URL,
$size,
$backgroundColor,
count($imageParts) > 0 ? '?text=' . urlencode(implode(' ', $imageParts)) : '',
);
}










public static function image(
$dir = null,
$width = 640,
$height = 480,
$category = null,
$fullPath = true,
$randomize = true,
$word = null,
$gray = false,
$format = 'png'
) {
trigger_deprecation(
'fakerphp/faker',
'1.20',
'Provider is deprecated and will no longer be available in Faker 2. Please use a custom provider instead',
);

$dir = null === $dir ? sys_get_temp_dir() : $dir; 


if (!is_dir($dir) || !is_writable($dir)) {
throw new \InvalidArgumentException(sprintf('Cannot write to directory "%s"', $dir));
}



$name = md5(uniqid(empty($_SERVER['SERVER_ADDR']) ? '' : $_SERVER['SERVER_ADDR'], true));
$filename = sprintf('%s.%s', $name, $format);
$filepath = $dir . DIRECTORY_SEPARATOR . $filename;

$url = static::imageUrl($width, $height, $category, $randomize, $word, $gray, $format);


if (function_exists('curl_exec')) {

$fp = fopen($filepath, 'w');
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_FILE, $fp);
$success = curl_exec($ch) && curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
fclose($fp);
curl_close($ch);

if (!$success) {
unlink($filepath);


return false;
}
} elseif (ini_get('allow_url_fopen')) {

$success = copy($url, $filepath);

if (!$success) {

return false;
}
} else {
return new \RuntimeException('The image formatter downloads an image from a remote HTTP server. Therefore, it requires that PHP can request remote hosts, either via cURL or fopen()');
}

return $fullPath ? $filepath : $filename;
}

public static function getFormats(): array
{
trigger_deprecation(
'fakerphp/faker',
'1.20',
'Provider is deprecated and will no longer be available in Faker 2. Please use a custom provider instead',
);

return array_keys(static::getFormatConstants());
}

public static function getFormatConstants(): array
{
trigger_deprecation(
'fakerphp/faker',
'1.20',
'Provider is deprecated and will no longer be available in Faker 2. Please use a custom provider instead',
);

return [
static::FORMAT_JPG => constant('IMAGETYPE_JPEG'),
static::FORMAT_JPEG => constant('IMAGETYPE_JPEG'),
static::FORMAT_PNG => constant('IMAGETYPE_PNG'),
];
}
}
