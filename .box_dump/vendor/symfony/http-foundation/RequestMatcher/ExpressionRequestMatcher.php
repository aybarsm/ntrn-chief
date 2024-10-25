<?php










namespace Symfony\Component\HttpFoundation\RequestMatcher;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;






class ExpressionRequestMatcher implements RequestMatcherInterface
{
public function __construct(
private ExpressionLanguage $language,
private Expression|string $expression,
) {
}

public function matches(Request $request): bool
{
return $this->language->evaluate($this->expression, [
'request' => $request,
'method' => $request->getMethod(),
'path' => rawurldecode($request->getPathInfo()),
'host' => $request->getHost(),
'ip' => $request->getClientIp(),
'attributes' => $request->attributes->all(),
]);
}
}
