<?php










namespace Symfony\Component\HttpFoundation;






class ChainRequestMatcher implements RequestMatcherInterface
{



public function __construct(private iterable $matchers)
{
}

public function matches(Request $request): bool
{
foreach ($this->matchers as $matcher) {
if (!$matcher->matches($request)) {
return false;
}
}

return true;
}
}
