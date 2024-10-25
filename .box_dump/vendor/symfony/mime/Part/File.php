<?php










namespace Symfony\Component\Mime\Part;

use Symfony\Component\Mime\MimeTypes;




class File
{
private static MimeTypes $mimeTypes;

public function __construct(
private string $path,
private ?string $filename = null,
) {
}

public function getPath(): string
{
return $this->path;
}

public function getContentType(): string
{
$ext = strtolower(pathinfo($this->path, \PATHINFO_EXTENSION));
self::$mimeTypes ??= new MimeTypes();

return self::$mimeTypes->getMimeTypes($ext)[0] ?? 'application/octet-stream';
}

public function getSize(): int
{
return filesize($this->path);
}

public function getFilename(): string
{
return $this->filename ??= basename($this->getPath());
}
}
