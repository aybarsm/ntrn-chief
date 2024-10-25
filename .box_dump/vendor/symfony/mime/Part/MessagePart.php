<?php










namespace Symfony\Component\Mime\Part;

use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;






class MessagePart extends DataPart
{
public function __construct(
private RawMessage $message,
) {
if ($message instanceof Message) {
$name = $message->getHeaders()->getHeaderBody('Subject').'.eml';
} else {
$name = 'email.eml';
}
parent::__construct('', $name);
}

public function getMediaType(): string
{
return 'message';
}

public function getMediaSubtype(): string
{
return 'rfc822';
}

public function getBody(): string
{
return $this->message->toString();
}

public function bodyToString(): string
{
return $this->getBody();
}

public function bodyToIterable(): iterable
{
return $this->message->toIterable();
}

public function __sleep(): array
{
return ['message'];
}

public function __wakeup(): void
{
$this->__construct($this->message);
}
}
