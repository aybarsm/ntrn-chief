<?php










namespace Symfony\Component\HttpKernel\Controller\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;






final class SessionValueResolver implements ValueResolverInterface
{
public function resolve(Request $request, ArgumentMetadata $argument): array
{
if (!$request->hasSession()) {
return [];
}

$type = $argument->getType();
if (SessionInterface::class !== $type && !is_subclass_of($type, SessionInterface::class)) {
return [];
}

return $request->getSession() instanceof $type ? [$request->getSession()] : [];
}
}
