<?php










namespace Symfony\Component\Mime;

use Symfony\Component\Mime\Exception\InvalidArgumentException;
use Symfony\Component\Mime\Exception\LogicException;






class FileinfoMimeTypeGuesser implements MimeTypeGuesserInterface
{





public function __construct(
private ?string $magicFile = null,
) {
}

public function isGuesserSupported(): bool
{
return \function_exists('finfo_open');
}

public function guessMimeType(string $path): ?string
{
if (!is_file($path) || !is_readable($path)) {
throw new InvalidArgumentException(sprintf('The "%s" file does not exist or is not readable.', $path));
}

if (!$this->isGuesserSupported()) {
throw new LogicException(sprintf('The "%s" guesser is not supported.', __CLASS__));
}

if (false === $finfo = new \finfo(\FILEINFO_MIME_TYPE, $this->magicFile)) {
return null;
}
$mimeType = $finfo->file($path);

if ($mimeType && 0 === (\strlen($mimeType) % 2)) {
$mimeStart = substr($mimeType, 0, \strlen($mimeType) >> 1);
$mimeType = $mimeStart.$mimeStart === $mimeType ? $mimeStart : $mimeType;
}

return $mimeType;
}
}
