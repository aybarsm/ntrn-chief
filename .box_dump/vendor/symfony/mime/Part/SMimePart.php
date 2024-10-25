<?php










namespace Symfony\Component\Mime\Part;

use Symfony\Component\Mime\Header\Headers;




class SMimePart extends AbstractPart
{

protected Headers $_headers;

public function __construct(
private iterable|string $body,
private string $type,
private string $subtype,
private array $parameters,
) {
parent::__construct();
}

public function getMediaType(): string
{
return $this->type;
}

public function getMediaSubtype(): string
{
return $this->subtype;
}

public function bodyToString(): string
{
if (\is_string($this->body)) {
return $this->body;
}

$body = '';
foreach ($this->body as $chunk) {
$body .= $chunk;
}
$this->body = $body;

return $body;
}

public function bodyToIterable(): iterable
{
if (\is_string($this->body)) {
yield $this->body;

return;
}

$body = '';
foreach ($this->body as $chunk) {
$body .= $chunk;
yield $chunk;
}
$this->body = $body;
}

public function getPreparedHeaders(): Headers
{
$headers = clone parent::getHeaders();

$headers->setHeaderBody('Parameterized', 'Content-Type', $this->getMediaType().'/'.$this->getMediaSubtype());

foreach ($this->parameters as $name => $value) {
$headers->setHeaderParameter('Content-Type', $name, $value);
}

return $headers;
}

public function __sleep(): array
{

if (is_iterable($this->body)) {
$this->body = $this->bodyToString();
}

$this->_headers = $this->getHeaders();

return ['_headers', 'body', 'type', 'subtype', 'parameters'];
}

public function __wakeup(): void
{
$r = new \ReflectionProperty(AbstractPart::class, 'headers');
$r->setValue($this, $this->_headers);
unset($this->_headers);
}
}
