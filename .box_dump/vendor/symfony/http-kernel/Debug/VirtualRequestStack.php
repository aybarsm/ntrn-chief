<?php










namespace Symfony\Component\HttpKernel\Debug;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;








final class VirtualRequestStack extends RequestStack
{
public function __construct(
private readonly RequestStack $decorated,
) {
}

public function push(Request $request): void
{
if ($request->attributes->has('_virtual_type')) {
if ($this->decorated->getCurrentRequest()) {
throw new \LogicException('Cannot mix virtual and HTTP requests.');
}

parent::push($request);

return;
}

$this->decorated->push($request);
}

public function pop(): ?Request
{
return $this->decorated->pop() ?? parent::pop();
}

public function getCurrentRequest(): ?Request
{
return $this->decorated->getCurrentRequest() ?? parent::getCurrentRequest();
}

public function getMainRequest(): ?Request
{
return $this->decorated->getMainRequest() ?? parent::getMainRequest();
}

public function getParentRequest(): ?Request
{
return $this->decorated->getParentRequest() ?? parent::getParentRequest();
}
}
