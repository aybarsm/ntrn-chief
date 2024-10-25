<?php










namespace Symfony\Component\HttpFoundation\RequestMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;






class IsJsonRequestMatcher implements RequestMatcherInterface
{
public function matches(Request $request): bool
{
return json_validate($request->getContent());
}
}
