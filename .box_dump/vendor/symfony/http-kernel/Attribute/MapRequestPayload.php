<?php










namespace Symfony\Component\HttpKernel\Attribute;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestPayloadValueResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Constraints\GroupSequence;






#[\Attribute(\Attribute::TARGET_PARAMETER)]
class MapRequestPayload extends ValueResolver
{
public ArgumentMetadata $metadata;









public function __construct(
public readonly array|string|null $acceptFormat = null,
public readonly array $serializationContext = [],
public readonly string|GroupSequence|array|null $validationGroups = null,
string $resolver = RequestPayloadValueResolver::class,
public readonly int $validationFailedStatusCode = Response::HTTP_UNPROCESSABLE_ENTITY,
public readonly ?string $type = null,
) {
parent::__construct($resolver);
}
}
