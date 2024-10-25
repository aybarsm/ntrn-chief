<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Rfc4122;

/**
@psalm-immutable







*/
trait MaxTrait
{



abstract public function getBytes(): string;




public function isMax(): bool
{
return $this->getBytes() === "\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff\xff";
}
}
