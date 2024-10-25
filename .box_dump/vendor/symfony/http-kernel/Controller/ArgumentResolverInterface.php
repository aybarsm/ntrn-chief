<?php










namespace Symfony\Component\HttpKernel\Controller;

use Symfony\Component\HttpFoundation\Request;







interface ArgumentResolverInterface
{





public function getArguments(Request $request, callable $controller, ?\ReflectionFunctionAbstract $reflector = null): array;
}