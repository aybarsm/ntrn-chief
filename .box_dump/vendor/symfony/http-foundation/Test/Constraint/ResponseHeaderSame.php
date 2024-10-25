<?php










namespace Symfony\Component\HttpFoundation\Test\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\HttpFoundation\Response;

final class ResponseHeaderSame extends Constraint
{
private string $headerName;
private string $expectedValue;

public function __construct(string $headerName, string $expectedValue)
{
$this->headerName = $headerName;
$this->expectedValue = $expectedValue;
}

public function toString(): string
{
return sprintf('has header "%s" with value "%s"', $this->headerName, $this->expectedValue);
}




protected function matches($response): bool
{
return $this->expectedValue === $response->headers->get($this->headerName, null);
}




protected function failureDescription($response): string
{
return 'the Response '.$this->toString();
}
}
