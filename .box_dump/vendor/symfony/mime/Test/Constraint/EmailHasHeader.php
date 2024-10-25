<?php










namespace Symfony\Component\Mime\Test\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\Mime\RawMessage;

final class EmailHasHeader extends Constraint
{
public function __construct(
private string $headerName,
) {
}

public function toString(): string
{
return sprintf('has header "%s"', $this->headerName);
}




protected function matches($message): bool
{
if (RawMessage::class === $message::class) {
throw new \LogicException('Unable to test a message header on a RawMessage instance.');
}

return $message->getHeaders()->has($this->headerName);
}




protected function failureDescription($message): string
{
return 'the Email '.$this->toString();
}
}
