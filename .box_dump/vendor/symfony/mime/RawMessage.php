<?php










namespace Symfony\Component\Mime;

use Symfony\Component\Mime\Exception\LogicException;




class RawMessage
{
private bool $isGeneratorClosed;




public function __construct(
private $message,
) {
}

public function __destruct()
{
if (\is_resource($this->message)) {
fclose($this->message);
}
}

public function toString(): string
{
if (\is_string($this->message)) {
return $this->message;
}

if (\is_resource($this->message)) {
return stream_get_contents($this->message, -1, 0);
}

$message = '';
foreach ($this->message as $chunk) {
$message .= $chunk;
}

return $this->message = $message;
}

public function toIterable(): iterable
{
if ($this->isGeneratorClosed ?? false) {
throw new LogicException('Unable to send the email as its generator is already closed.');
}

if (\is_string($this->message)) {
yield $this->message;

return;
}

if (\is_resource($this->message)) {
rewind($this->message);
while ($line = fgets($this->message)) {
yield $line;
}

return;
}

if ($this->message instanceof \Generator) {
$message = fopen('php://temp', 'w+');
foreach ($this->message as $chunk) {
fwrite($message, $chunk);
yield $chunk;
}
$this->isGeneratorClosed = !$this->message->valid();
$this->message = $message;

return;
}

foreach ($this->message as $chunk) {
yield $chunk;
}
}




public function ensureValidity(): void
{
}

public function __serialize(): array
{
return [$this->toString()];
}

public function __unserialize(array $data): void
{
[$this->message] = $data;
}
}
