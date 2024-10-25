<?php










namespace Symfony\Component\Mime;

use Symfony\Component\Mime\Exception\InvalidArgumentException;
use Symfony\Component\Mime\Exception\LogicException;






class FileBinaryMimeTypeGuesser implements MimeTypeGuesserInterface
{








public function __construct(
private string $cmd = 'file -b --mime -- %s 2>/dev/null',
) {
}

public function isGuesserSupported(): bool
{
static $supported = null;

if (null !== $supported) {
return $supported;
}

if ('\\' === \DIRECTORY_SEPARATOR || !\function_exists('passthru') || !\function_exists('escapeshellarg')) {
return $supported = false;
}

ob_start();
passthru('command -v file', $exitStatus);
$binPath = trim(ob_get_clean());

return $supported = 0 === $exitStatus && '' !== $binPath;
}

public function guessMimeType(string $path): ?string
{
if (!is_file($path) || !is_readable($path)) {
throw new InvalidArgumentException(sprintf('The "%s" file does not exist or is not readable.', $path));
}

if (!$this->isGuesserSupported()) {
throw new LogicException(sprintf('The "%s" guesser is not supported.', __CLASS__));
}

ob_start();


passthru(sprintf($this->cmd, escapeshellarg((str_starts_with($path, '-') ? './' : '').$path)), $return);
if ($return > 0) {
ob_end_clean();

return null;
}

$type = trim(ob_get_clean());

if (!preg_match('#^([a-z0-9\-]+/[a-z0-9\-\+\.]+)#i', $type, $match)) {

return null;
}

return $match[1];
}
}
