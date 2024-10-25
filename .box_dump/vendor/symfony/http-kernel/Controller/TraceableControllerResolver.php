<?php










namespace Symfony\Component\HttpKernel\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Stopwatch\Stopwatch;




class TraceableControllerResolver implements ControllerResolverInterface
{
public function __construct(
private ControllerResolverInterface $resolver,
private Stopwatch $stopwatch,
) {
}

public function getController(Request $request): callable|false
{
$e = $this->stopwatch->start('controller.get_callable');

try {
return $this->resolver->getController($request);
} finally {
$e->stop();
}
}
}
