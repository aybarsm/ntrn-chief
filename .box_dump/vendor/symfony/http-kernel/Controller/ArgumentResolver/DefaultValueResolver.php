<?php










namespace Symfony\Component\HttpKernel\Controller\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;






final class DefaultValueResolver implements ValueResolverInterface
{
public function resolve(Request $request, ArgumentMetadata $argument): array
{
if ($argument->hasDefaultValue()) {
return [$argument->getDefaultValue()];
}

if (null !== $argument->getType() && $argument->isNullable() && !$argument->isVariadic()) {
return [null];
}

return [];
}
}
