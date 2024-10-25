<?php










namespace Symfony\Component\HttpKernel\Attribute;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\QueryParameterValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;







#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class MapQueryParameter extends ValueResolver
{









public function __construct(
public ?string $name = null,
public ?int $filter = null,
public int $flags = 0,
public array $options = [],
string $resolver = QueryParameterValueResolver::class,
public int $validationFailedStatusCode = Response::HTTP_NOT_FOUND,
) {
parent::__construct($resolver);
}
}
