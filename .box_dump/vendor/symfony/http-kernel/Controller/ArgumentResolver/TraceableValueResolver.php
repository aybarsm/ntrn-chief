<?php










namespace Symfony\Component\HttpKernel\Controller\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Stopwatch\Stopwatch;






final class TraceableValueResolver implements ValueResolverInterface
{
public function __construct(
private ValueResolverInterface $inner,
private Stopwatch $stopwatch,
) {
}

public function resolve(Request $request, ArgumentMetadata $argument): iterable
{
$method = $this->inner::class.'::'.__FUNCTION__;
$this->stopwatch->start($method, 'controller.argument_value_resolver');

yield from $this->inner->resolve($request, $argument);

$this->stopwatch->stop($method);
}
}
