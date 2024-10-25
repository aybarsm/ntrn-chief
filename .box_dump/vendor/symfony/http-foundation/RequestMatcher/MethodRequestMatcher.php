<?php










namespace Symfony\Component\HttpFoundation\RequestMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;






class MethodRequestMatcher implements RequestMatcherInterface
{



private array $methods = [];





public function __construct(array|string $methods)
{
$this->methods = array_reduce(array_map('strtoupper', (array) $methods), static fn (array $methods, string $method) => array_merge($methods, preg_split('/\s*,\s*/', $method)), []);
}

public function matches(Request $request): bool
{
if (!$this->methods) {
return true;
}

return \in_array($request->getMethod(), $this->methods, true);
}
}
