<?php










namespace Symfony\Component\HttpFoundation\RequestMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;






class HeaderRequestMatcher implements RequestMatcherInterface
{



private array $headers;





public function __construct(array|string $headers)
{
$this->headers = array_reduce((array) $headers, static fn (array $headers, string $header) => array_merge($headers, preg_split('/\s*,\s*/', $header)), []);
}

public function matches(Request $request): bool
{
if (!$this->headers) {
return true;
}

foreach ($this->headers as $header) {
if (!$request->headers->has($header)) {
return false;
}
}

return true;
}
}
