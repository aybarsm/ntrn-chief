<?php










namespace Symfony\Component\HttpFoundation\Test\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\HttpFoundation\Response;

final class ResponseStatusCodeSame extends Constraint
{
private int $statusCode;

public function __construct(int $statusCode, private readonly bool $verbose = true)
{
$this->statusCode = $statusCode;
}

public function toString(): string
{
return 'status code is '.$this->statusCode;
}




protected function matches($response): bool
{
return $this->statusCode === $response->getStatusCode();
}




protected function failureDescription($response): string
{
return 'the Response '.$this->toString();
}




protected function additionalFailureDescription($response): string
{
return $this->verbose ? (string) $response : explode("\r\n\r\n", (string) $response)[0];
}
}
