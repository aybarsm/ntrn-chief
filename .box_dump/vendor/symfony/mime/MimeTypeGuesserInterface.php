<?php










namespace Symfony\Component\Mime;






interface MimeTypeGuesserInterface
{



public function isGuesserSupported(): bool;







public function guessMimeType(string $path): ?string;
}
