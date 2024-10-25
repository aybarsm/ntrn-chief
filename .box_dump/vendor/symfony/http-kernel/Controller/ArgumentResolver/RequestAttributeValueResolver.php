<?php










namespace Symfony\Component\HttpKernel\Controller\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;






final class RequestAttributeValueResolver implements ValueResolverInterface
{
public function resolve(Request $request, ArgumentMetadata $argument): array
{
return !$argument->isVariadic() && $request->attributes->has($argument->getName()) ? [$request->attributes->get($argument->getName())] : [];
}
}
