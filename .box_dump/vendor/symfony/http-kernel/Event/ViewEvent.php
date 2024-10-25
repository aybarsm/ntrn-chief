<?php










namespace Symfony\Component\HttpKernel\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;










final class ViewEvent extends RequestEvent
{
public function __construct(
HttpKernelInterface $kernel,
Request $request,
int $requestType,
private mixed $controllerResult,
public readonly ?ControllerArgumentsEvent $controllerArgumentsEvent = null,
) {
parent::__construct($kernel, $request, $requestType);
}

public function getControllerResult(): mixed
{
return $this->controllerResult;
}

public function setControllerResult(mixed $controllerResult): void
{
$this->controllerResult = $controllerResult;
}
}
