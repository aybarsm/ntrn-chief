<?php

namespace Illuminate\Http\Testing;

use LogicException;

class FileFactory
{








public function create($name, $kilobytes = 0, $mimeType = null)
{
if (is_string($kilobytes)) {
return $this->createWithContent($name, $kilobytes);
}

return tap(new File($name, tmpfile()), function ($file) use ($kilobytes, $mimeType) {
$file->sizeToReport = $kilobytes * 1024;
$file->mimeTypeToReport = $mimeType;
});
}








public function createWithContent($name, $content)
{
$tmpfile = tmpfile();

fwrite($tmpfile, $content);

return tap(new File($name, $tmpfile), function ($file) use ($tmpfile) {
$file->sizeToReport = fstat($tmpfile)['size'];
});
}











public function image($name, $width = 10, $height = 10)
{
return new File($name, $this->generateImage(
$width, $height, pathinfo($name, PATHINFO_EXTENSION)
));
}











protected function generateImage($width, $height, $extension)
{
if (! function_exists('imagecreatetruecolor')) {
throw new LogicException('GD extension is not installed.');
}

return tap(tmpfile(), function ($temp) use ($width, $height, $extension) {
ob_start();

$extension = in_array($extension, ['jpeg', 'png', 'gif', 'webp', 'wbmp', 'bmp'])
? strtolower($extension)
: 'jpeg';

$image = imagecreatetruecolor($width, $height);

if (! function_exists($functionName = "image{$extension}")) {
ob_get_clean();

throw new LogicException("{$functionName} function is not defined and image cannot be generated.");
}

call_user_func($functionName, $image);

fwrite($temp, ob_get_clean());
});
}
}
