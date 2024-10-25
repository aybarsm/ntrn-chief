<?php










namespace Symfony\Component\HttpKernel\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;













final class ControllerArgumentsEvent extends KernelEvent
{
private ControllerEvent $controllerEvent;
private array $namedArguments;

public function __construct(
HttpKernelInterface $kernel,
callable|ControllerEvent $controller,
private array $arguments,
Request $request,
?int $requestType,
) {
parent::__construct($kernel, $request, $requestType);

if (!$controller instanceof ControllerEvent) {
$controller = new ControllerEvent($kernel, $controller, $request, $requestType);
}

$this->controllerEvent = $controller;
}

public function getController(): callable
{
return $this->controllerEvent->getController();
}




public function setController(callable $controller, ?array $attributes = null): void
{
$this->controllerEvent->setController($controller, $attributes);
unset($this->namedArguments);
}

public function getArguments(): array
{
return $this->arguments;
}

public function setArguments(array $arguments): void
{
$this->arguments = $arguments;
unset($this->namedArguments);
}

public function getNamedArguments(): array
{
if (isset($this->namedArguments)) {
return $this->namedArguments;
}

$namedArguments = [];
$arguments = $this->arguments;

foreach ($this->controllerEvent->getControllerReflector()->getParameters() as $i => $param) {
if ($param->isVariadic()) {
$namedArguments[$param->name] = \array_slice($arguments, $i);
break;
}
if (\array_key_exists($i, $arguments)) {
$namedArguments[$param->name] = $arguments[$i];
} elseif ($param->isDefaultvalueAvailable()) {
$namedArguments[$param->name] = $param->getDefaultValue();
}
}

return $this->namedArguments = $namedArguments;
}

/**
@template
@psalm-return(T is null ? array<class-string, list<object>> : list<object>)





*/
public function getAttributes(?string $className = null): array
{
return $this->controllerEvent->getAttributes($className);
}
}
