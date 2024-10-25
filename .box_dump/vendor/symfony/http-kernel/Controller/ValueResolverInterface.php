<?php










namespace Symfony\Component\HttpKernel\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;






interface ValueResolverInterface
{



public function resolve(Request $request, ArgumentMetadata $argument): iterable;
}
