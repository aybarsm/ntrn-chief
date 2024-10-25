<?php










namespace Symfony\Component\HttpKernel\Attribute;




#[\Attribute(\Attribute::TARGET_CLASS)]
class AsTargetedValueResolver
{



public function __construct(public readonly ?string $name = null)
{
}
}
