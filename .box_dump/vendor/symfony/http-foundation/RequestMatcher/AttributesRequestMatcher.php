<?php










namespace Symfony\Component\HttpFoundation\RequestMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;






class AttributesRequestMatcher implements RequestMatcherInterface
{



public function __construct(private array $regexps)
{
}

public function matches(Request $request): bool
{
foreach ($this->regexps as $key => $regexp) {
$attribute = $request->attributes->get($key);
if (!\is_string($attribute)) {
return false;
}
if (!preg_match('{'.$regexp.'}', $attribute)) {
return false;
}
}

return true;
}
}
