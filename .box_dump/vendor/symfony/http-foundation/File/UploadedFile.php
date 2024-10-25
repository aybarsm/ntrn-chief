<?php










namespace Symfony\Component\HttpFoundation\File;

use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;
use Symfony\Component\HttpFoundation\File\Exception\ExtensionFileException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoTmpDirFileException;
use Symfony\Component\HttpFoundation\File\Exception\PartialFileException;
use Symfony\Component\Mime\MimeTypes;








class UploadedFile extends File
{
private bool $test;
private string $originalName;
private string $mimeType;
private int $error;
private string $originalPath;

























public function __construct(string $path, string $originalName, ?string $mimeType = null, ?int $error = null, bool $test = false)
{
$this->originalName = $this->getName($originalName);
$this->originalPath = strtr($originalName, '\\', '/');
$this->mimeType = $mimeType ?: 'application/octet-stream';
$this->error = $error ?: \UPLOAD_ERR_OK;
$this->test = $test;

parent::__construct($path, \UPLOAD_ERR_OK === $this->error);
}







public function getClientOriginalName(): string
{
return $this->originalName;
}







public function getClientOriginalExtension(): string
{
return pathinfo($this->originalName, \PATHINFO_EXTENSION);
}











public function getClientOriginalPath(): string
{
return $this->originalPath;
}












public function getClientMimeType(): string
{
return $this->mimeType;
}
















public function guessClientExtension(): ?string
{
if (!class_exists(MimeTypes::class)) {
throw new \LogicException('You cannot guess the extension as the Mime component is not installed. Try running "composer require symfony/mime".');
}

return MimeTypes::getDefault()->getExtensions($this->getClientMimeType())[0] ?? null;
}







public function getError(): int
{
return $this->error;
}




public function isValid(): bool
{
$isOk = \UPLOAD_ERR_OK === $this->error;

return $this->test ? $isOk : $isOk && is_uploaded_file($this->getPathname());
}






public function move(string $directory, ?string $name = null): File
{
if ($this->isValid()) {
if ($this->test) {
return parent::move($directory, $name);
}

$target = $this->getTargetFile($directory, $name);

set_error_handler(function ($type, $msg) use (&$error) { $error = $msg; });
try {
$moved = move_uploaded_file($this->getPathname(), $target);
} finally {
restore_error_handler();
}
if (!$moved) {
throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s).', $this->getPathname(), $target, strip_tags($error)));
}

@chmod($target, 0666 & ~umask());

return $target;
}

switch ($this->error) {
case \UPLOAD_ERR_INI_SIZE:
throw new IniSizeFileException($this->getErrorMessage());
case \UPLOAD_ERR_FORM_SIZE:
throw new FormSizeFileException($this->getErrorMessage());
case \UPLOAD_ERR_PARTIAL:
throw new PartialFileException($this->getErrorMessage());
case \UPLOAD_ERR_NO_FILE:
throw new NoFileException($this->getErrorMessage());
case \UPLOAD_ERR_CANT_WRITE:
throw new CannotWriteFileException($this->getErrorMessage());
case \UPLOAD_ERR_NO_TMP_DIR:
throw new NoTmpDirFileException($this->getErrorMessage());
case \UPLOAD_ERR_EXTENSION:
throw new ExtensionFileException($this->getErrorMessage());
}

throw new FileException($this->getErrorMessage());
}






public static function getMaxFilesize(): int|float
{
$sizePostMax = self::parseFilesize(\ini_get('post_max_size'));
$sizeUploadMax = self::parseFilesize(\ini_get('upload_max_filesize'));

return min($sizePostMax ?: \PHP_INT_MAX, $sizeUploadMax ?: \PHP_INT_MAX);
}

private static function parseFilesize(string $size): int|float
{
if ('' === $size) {
return 0;
}

$size = strtolower($size);

$max = ltrim($size, '+');
if (str_starts_with($max, '0x')) {
$max = \intval($max, 16);
} elseif (str_starts_with($max, '0')) {
$max = \intval($max, 8);
} else {
$max = (int) $max;
}

switch (substr($size, -1)) {
case 't': $max *= 1024;

case 'g': $max *= 1024;

case 'm': $max *= 1024;

case 'k': $max *= 1024;
}

return $max;
}




public function getErrorMessage(): string
{
static $errors = [
\UPLOAD_ERR_INI_SIZE => 'The file "%s" exceeds your upload_max_filesize ini directive (limit is %d KiB).',
\UPLOAD_ERR_FORM_SIZE => 'The file "%s" exceeds the upload limit defined in your form.',
\UPLOAD_ERR_PARTIAL => 'The file "%s" was only partially uploaded.',
\UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
\UPLOAD_ERR_CANT_WRITE => 'The file "%s" could not be written on disk.',
\UPLOAD_ERR_NO_TMP_DIR => 'File could not be uploaded: missing temporary directory.',
\UPLOAD_ERR_EXTENSION => 'File upload was stopped by a PHP extension.',
];

$errorCode = $this->error;
$maxFilesize = \UPLOAD_ERR_INI_SIZE === $errorCode ? self::getMaxFilesize() / 1024 : 0;
$message = $errors[$errorCode] ?? 'The file "%s" was not uploaded due to an unknown error.';

return sprintf($message, $this->getClientOriginalName(), $maxFilesize);
}
}
