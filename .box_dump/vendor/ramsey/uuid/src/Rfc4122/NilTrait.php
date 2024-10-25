<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Rfc4122;

/**
@psalm-immutable







*/
trait NilTrait
{



abstract public function getBytes(): string;




public function isNil(): bool
{
return $this->getBytes() === "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
}
}
