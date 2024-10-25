<?php










namespace Symfony\Component\HttpKernel\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Stopwatch\Stopwatch;




class TraceableArgumentResolver implements ArgumentResolverInterface
{
public function __construct(
private ArgumentResolverInterface $resolver,
private Stopwatch $stopwatch,
) {
}

public function getArguments(Request $request, callable $controller, ?\ReflectionFunctionAbstract $reflector = null): array
{
$e = $this->stopwatch->start('controller.get_arguments');

try {
return $this->resolver->getArguments($request, $controller, $reflector);
} finally {
$e->stop();
}
}
}
