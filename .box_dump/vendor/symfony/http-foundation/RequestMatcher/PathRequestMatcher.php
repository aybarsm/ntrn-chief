<?php










namespace Symfony\Component\HttpFoundation\RequestMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;






class PathRequestMatcher implements RequestMatcherInterface
{
public function __construct(private string $regexp)
{
}

public function matches(Request $request): bool
{
return preg_match('{'.$this->regexp.'}', rawurldecode($request->getPathInfo()));
}
}
