<?php










namespace Symfony\Component\HttpKernel\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;










final class ResponseEvent extends KernelEvent
{
public function __construct(
HttpKernelInterface $kernel,
Request $request,
int $requestType,
private Response $response,
) {
parent::__construct($kernel, $request, $requestType);
}

public function getResponse(): Response
{
return $this->response;
}

public function setResponse(Response $response): void
{
$this->response = $response;
}
}
