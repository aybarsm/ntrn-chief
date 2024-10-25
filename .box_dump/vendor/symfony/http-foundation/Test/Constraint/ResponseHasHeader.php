<?php










namespace Symfony\Component\HttpFoundation\Test\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\HttpFoundation\Response;

final class ResponseHasHeader extends Constraint
{
private string $headerName;

public function __construct(string $headerName)
{
$this->headerName = $headerName;
}

public function toString(): string
{
return sprintf('has header "%s"', $this->headerName);
}




protected function matches($response): bool
{
return $response->headers->has($this->headerName);
}




protected function failureDescription($response): string
{
return 'the Response '.$this->toString();
}
}
