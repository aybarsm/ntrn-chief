<?php










namespace Symfony\Component\HttpFoundation\RequestMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;






class HostRequestMatcher implements RequestMatcherInterface
{
public function __construct(private string $regexp)
{
}

public function matches(Request $request): bool
{
return preg_match('{'.$this->regexp.'}i', $request->getHost());
}
}
