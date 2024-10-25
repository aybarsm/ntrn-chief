<?php










namespace Symfony\Component\HttpFoundation\Test\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;






final class ResponseFormatSame extends Constraint
{
private Request $request;
private ?string $format;

public function __construct(
Request $request,
?string $format,
private readonly bool $verbose = true,
) {
$this->request = $request;
$this->format = $format;
}

public function toString(): string
{
return 'format is '.($this->format ?? 'null');
}




protected function matches($response): bool
{
return $this->format === $this->request->getFormat($response->headers->get('Content-Type'));
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
