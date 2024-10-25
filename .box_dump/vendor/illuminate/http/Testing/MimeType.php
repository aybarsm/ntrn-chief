<?php

namespace Illuminate\Http\Testing;

use Illuminate\Support\Arr;
use Symfony\Component\Mime\MimeTypes;

class MimeType
{





private static $mime;






public static function getMimeTypes()
{
if (self::$mime === null) {
self::$mime = new MimeTypes;
}

return self::$mime;
}







public static function from($filename)
{
$extension = pathinfo($filename, PATHINFO_EXTENSION);

return self::get($extension);
}







public static function get($extension)
{
return Arr::first(self::getMimeTypes()->getMimeTypes($extension)) ?? 'application/octet-stream';
}







public static function search($mimeType)
{
return Arr::first(self::getMimeTypes()->getExtensions($mimeType));
}
}
