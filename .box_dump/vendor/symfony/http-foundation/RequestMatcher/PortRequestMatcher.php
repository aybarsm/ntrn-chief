<?php










namespace Symfony\Component\HttpFoundation\RequestMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;






class PortRequestMatcher implements RequestMatcherInterface
{
public function __construct(private int $port)
{
}

public function matches(Request $request): bool
{
return $request->getPort() === $this->port;
}
}
