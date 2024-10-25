<?php










namespace Symfony\Component\HttpFoundation\RequestMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;






class SchemeRequestMatcher implements RequestMatcherInterface
{



private array $schemes;





public function __construct(array|string $schemes)
{
$this->schemes = array_reduce(array_map('strtolower', (array) $schemes), static fn (array $schemes, string $scheme) => array_merge($schemes, preg_split('/\s*,\s*/', $scheme)), []);
}

public function matches(Request $request): bool
{
if (!$this->schemes) {
return true;
}

return \in_array($request->getScheme(), $this->schemes, true);
}
}
