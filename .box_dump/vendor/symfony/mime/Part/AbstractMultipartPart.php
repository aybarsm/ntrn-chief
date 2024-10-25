<?php










namespace Symfony\Component\Mime\Part;

use Symfony\Component\Mime\Header\Headers;




abstract class AbstractMultipartPart extends AbstractPart
{
private ?string $boundary = null;
private array $parts = [];

public function __construct(AbstractPart ...$parts)
{
parent::__construct();

foreach ($parts as $part) {
$this->parts[] = $part;
}
}




public function getParts(): array
{
return $this->parts;
}

public function getMediaType(): string
{
return 'multipart';
}

public function getPreparedHeaders(): Headers
{
$headers = parent::getPreparedHeaders();
$headers->setHeaderParameter('Content-Type', 'boundary', $this->getBoundary());

return $headers;
}

public function bodyToString(): string
{
$parts = $this->getParts();
$string = '';
foreach ($parts as $part) {
$string .= '--'.$this->getBoundary()."\r\n".$part->toString()."\r\n";
}
$string .= '--'.$this->getBoundary()."--\r\n";

return $string;
}

public function bodyToIterable(): iterable
{
$parts = $this->getParts();
foreach ($parts as $part) {
yield '--'.$this->getBoundary()."\r\n";
yield from $part->toIterable();
yield "\r\n";
}
yield '--'.$this->getBoundary()."--\r\n";
}

public function asDebugString(): string
{
$str = parent::asDebugString();
foreach ($this->getParts() as $part) {
$lines = explode("\n", $part->asDebugString());
$str .= "\n  └ ".array_shift($lines);
foreach ($lines as $line) {
$str .= "\n  |".$line;
}
}

return $str;
}

private function getBoundary(): string
{
return $this->boundary ??= strtr(base64_encode(random_bytes(6)), '+/', '-_');
}
}
