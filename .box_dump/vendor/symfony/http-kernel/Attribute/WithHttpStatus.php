<?php










namespace Symfony\Component\HttpKernel\Attribute;






#[\Attribute(\Attribute::TARGET_CLASS)]
class WithHttpStatus
{




public function __construct(
public readonly int $statusCode,
public readonly array $headers = [],
) {
}
}
