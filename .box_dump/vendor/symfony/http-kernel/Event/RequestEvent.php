<?php










namespace Symfony\Component\HttpKernel\Event;

use Symfony\Component\HttpFoundation\Response;










class RequestEvent extends KernelEvent
{
private ?Response $response = null;




public function getResponse(): ?Response
{
return $this->response;
}




public function setResponse(Response $response): void
{
$this->response = $response;

$this->stopPropagation();
}




public function hasResponse(): bool
{
return null !== $this->response;
}
}
