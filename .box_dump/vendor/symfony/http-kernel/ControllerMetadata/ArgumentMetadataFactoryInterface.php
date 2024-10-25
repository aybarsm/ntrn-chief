<?php










namespace Symfony\Component\HttpKernel\ControllerMetadata;






interface ArgumentMetadataFactoryInterface
{



public function createArgumentMetadata(string|object|array $controller, ?\ReflectionFunctionAbstract $reflector = null): array;
}
