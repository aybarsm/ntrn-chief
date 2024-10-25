<?php










namespace Symfony\Component\Mime\Test\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;

final class EmailAttachmentCount extends Constraint
{
public function __construct(
private int $expectedValue,
private ?string $transport = null,
) {
}

public function toString(): string
{
return sprintf('has sent "%d" attachment(s)', $this->expectedValue);
}




protected function matches($message): bool
{
if (RawMessage::class === $message::class || Message::class === $message::class) {
throw new \LogicException('Unable to test a message attachment on a RawMessage or Message instance.');
}

return $this->expectedValue === \count($message->getAttachments());
}




protected function failureDescription($message): string
{
return 'the Email '.$this->toString();
}
}
