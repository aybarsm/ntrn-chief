<?php










namespace Symfony\Component\HttpKernel\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;









final class TerminateEvent extends KernelEvent
{
public function __construct(
HttpKernelInterface $kernel,
Request $request,
private Response $response,
) {
parent::__construct($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
}

public function getResponse(): Response
{
return $this->response;
}
}
