<?php










namespace Symfony\Component\HttpKernel\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\EventDispatcher\Event;






class KernelEvent extends Event
{




public function __construct(
private HttpKernelInterface $kernel,
private Request $request,
private ?int $requestType,
) {
}




public function getKernel(): HttpKernelInterface
{
return $this->kernel;
}




public function getRequest(): Request
{
return $this->request;
}







public function getRequestType(): int
{
return $this->requestType;
}




public function isMainRequest(): bool
{
return HttpKernelInterface::MAIN_REQUEST === $this->requestType;
}
}
