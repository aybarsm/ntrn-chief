<?php










namespace Symfony\Component\HttpFoundation\RequestMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;






class QueryParameterRequestMatcher implements RequestMatcherInterface
{



private array $parameters;





public function __construct(array|string $parameters)
{
$this->parameters = array_reduce(array_map(strtolower(...), (array) $parameters), static fn (array $parameters, string $parameter) => array_merge($parameters, preg_split('/\s*,\s*/', $parameter)), []);
}

public function matches(Request $request): bool
{
if (!$this->parameters) {
return true;
}

return 0 === \count(array_diff_assoc($this->parameters, $request->query->keys()));
}
}
