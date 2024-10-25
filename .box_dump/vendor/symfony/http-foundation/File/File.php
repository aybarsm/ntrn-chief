<?php










namespace Symfony\Component\HttpFoundation\File;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Mime\MimeTypes;






class File extends \SplFileInfo
{








public function __construct(string $path, bool $checkPath = true)
{
if ($checkPath && !is_file($path)) {
throw new FileNotFoundException($path);
}

parent::__construct($path);
}












public function guessExtension(): ?string
{
if (!class_exists(MimeTypes::class)) {
throw new \LogicException('You cannot guess the extension as the Mime component is not installed. Try running "composer require symfony/mime".');
}

return MimeTypes::getDefault()->getExtensions($this->getMimeType())[0] ?? null;
}










public function getMimeType(): ?string
{
if (!class_exists(MimeTypes::class)) {
throw new \LogicException('You cannot guess the mime type as the Mime component is not installed. Try running "composer require symfony/mime".');
}

return MimeTypes::getDefault()->guessMimeType($this->getPathname());
}






public function move(string $directory, ?string $name = null): self
{
$target = $this->getTargetFile($directory, $name);

set_error_handler(function ($type, $msg) use (&$error) { $error = $msg; });
try {
$renamed = rename($this->getPathname(), $target);
} finally {
restore_error_handler();
}
if (!$renamed) {
throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s).', $this->getPathname(), $target, strip_tags($error)));
}

@chmod($target, 0666 & ~umask());

return $target;
}

public function getContent(): string
{
$content = file_get_contents($this->getPathname());

if (false === $content) {
throw new FileException(sprintf('Could not get the content of the file "%s".', $this->getPathname()));
}

return $content;
}

protected function getTargetFile(string $directory, ?string $name = null): self
{
if (!is_dir($directory)) {
if (false === @mkdir($directory, 0777, true) && !is_dir($directory)) {
throw new FileException(sprintf('Unable to create the "%s" directory.', $directory));
}
} elseif (!is_writable($directory)) {
throw new FileException(sprintf('Unable to write in the "%s" directory.', $directory));
}

$target = rtrim($directory, '/\\').\DIRECTORY_SEPARATOR.(null === $name ? $this->getBasename() : $this->getName($name));

return new self($target, false);
}




protected function getName(string $name): string
{
$originalName = str_replace('\\', '/', $name);
$pos = strrpos($originalName, '/');
$originalName = false === $pos ? $originalName : substr($originalName, $pos + 1);

return $originalName;
}
}
