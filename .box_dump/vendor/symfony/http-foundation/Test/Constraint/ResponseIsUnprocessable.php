<?php










namespace Symfony\Component\HttpFoundation\Test\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\HttpFoundation\Response;

final class ResponseIsUnprocessable extends Constraint
{



public function __construct(private readonly bool $verbose = true)
{
}

public function toString(): string
{
return 'is unprocessable';
}




protected function matches($other): bool
{
return Response::HTTP_UNPROCESSABLE_ENTITY === $other->getStatusCode();
}




protected function failureDescription($other): string
{
return 'the Response '.$this->toString();
}




protected function additionalFailureDescription($response): string
{
return $this->verbose ? (string) $response : explode("\r\n\r\n", (string) $response)[0];
}
}
