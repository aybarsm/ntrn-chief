<?php










namespace Symfony\Component\HttpKernel\Attribute;

use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;




#[\Attribute(\Attribute::TARGET_PARAMETER | \Attribute::IS_REPEATABLE)]
class ValueResolver
{




public function __construct(
public string $resolver,
public bool $disabled = false,
) {
}
}
