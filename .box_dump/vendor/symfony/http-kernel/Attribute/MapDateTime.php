<?php










namespace Symfony\Component\HttpKernel\Attribute;

use Symfony\Component\HttpKernel\Controller\ArgumentResolver\DateTimeValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;




#[\Attribute(\Attribute::TARGET_PARAMETER)]
class MapDateTime extends ValueResolver
{





public function __construct(
public readonly ?string $format = null,
bool $disabled = false,
string $resolver = DateTimeValueResolver::class,
) {
parent::__construct($resolver, $disabled);
}
}
