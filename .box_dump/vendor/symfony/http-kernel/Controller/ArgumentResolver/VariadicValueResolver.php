<?php










namespace Symfony\Component\HttpKernel\Controller\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;






final class VariadicValueResolver implements ValueResolverInterface
{
public function resolve(Request $request, ArgumentMetadata $argument): array
{
if (!$argument->isVariadic() || !$request->attributes->has($argument->getName())) {
return [];
}

$values = $request->attributes->get($argument->getName());

if (!\is_array($values)) {
throw new \InvalidArgumentException(sprintf('The action argument "...$%1$s" is required to be an array, the request attribute "%1$s" contains a type of "%2$s" instead.', $argument->getName(), get_debug_type($values)));
}

return $values;
}
}
