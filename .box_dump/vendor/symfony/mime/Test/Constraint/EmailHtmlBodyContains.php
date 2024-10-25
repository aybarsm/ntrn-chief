<?php










namespace Symfony\Component\Mime\Test\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;

final class EmailHtmlBodyContains extends Constraint
{
public function __construct(
private string $expectedText,
) {
}

public function toString(): string
{
return sprintf('contains "%s"', $this->expectedText);
}




protected function matches($message): bool
{
if (RawMessage::class === $message::class || Message::class === $message::class) {
throw new \LogicException('Unable to test a message HTML body on a RawMessage or Message instance.');
}

return str_contains($message->getHtmlBody(), $this->expectedText);
}




protected function failureDescription($message): string
{
return 'the Email HTML body '.$this->toString();
}
}
