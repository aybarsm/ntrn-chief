<?php










namespace Symfony\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;






class RequestStack
{



private array $requests = [];







public function push(Request $request): void
{
$this->requests[] = $request;
}









public function pop(): ?Request
{
if (!$this->requests) {
return null;
}

return array_pop($this->requests);
}

public function getCurrentRequest(): ?Request
{
return end($this->requests) ?: null;
}








public function getMainRequest(): ?Request
{
if (!$this->requests) {
return null;
}

return $this->requests[0];
}










public function getParentRequest(): ?Request
{
$pos = \count($this->requests) - 2;

return $this->requests[$pos] ?? null;
}






public function getSession(): SessionInterface
{
if ((null !== $request = end($this->requests) ?: null) && $request->hasSession()) {
return $request->getSession();
}

throw new SessionNotFoundException();
}
}
