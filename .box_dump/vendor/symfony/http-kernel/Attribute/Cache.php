<?php










namespace Symfony\Component\HttpKernel\Attribute;










#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
final class Cache
{
public function __construct(



public ?string $expires = null,





public int|string|null $maxage = null,





public int|string|null $smaxage = null,





public ?bool $public = null,





public bool $mustRevalidate = false,











public array $vary = [],









public ?string $lastModified = null,









public ?string $etag = null,





public int|string|null $maxStale = null,





public int|string|null $staleWhileRevalidate = null,





public int|string|null $staleIfError = null,
) {
}
}
