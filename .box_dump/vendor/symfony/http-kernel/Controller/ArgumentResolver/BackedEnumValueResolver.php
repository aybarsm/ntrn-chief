<?php










namespace Symfony\Component\HttpKernel\Controller\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;







final class BackedEnumValueResolver implements ValueResolverInterface
{
public function resolve(Request $request, ArgumentMetadata $argument): iterable
{
if (!is_subclass_of($argument->getType(), \BackedEnum::class)) {
return [];
}

if ($argument->isVariadic()) {

return [];
}




if (!$request->attributes->has($argument->getName())) {
return [];
}

$value = $request->attributes->get($argument->getName());

if (null === $value) {
return [null];
}

if ($value instanceof \BackedEnum) {
return [$value];
}

if (!\is_int($value) && !\is_string($value)) {
throw new \LogicException(sprintf('Could not resolve the "%s $%s" controller argument: expecting an int or string, got "%s".', $argument->getType(), $argument->getName(), get_debug_type($value)));
}


$enumType = $argument->getType();

try {
return [$enumType::from($value)];
} catch (\ValueError|\TypeError $e) {
throw new NotFoundHttpException(sprintf('Could not resolve the "%s $%s" controller argument: ', $argument->getType(), $argument->getName()).$e->getMessage(), $e);
}
}
}
