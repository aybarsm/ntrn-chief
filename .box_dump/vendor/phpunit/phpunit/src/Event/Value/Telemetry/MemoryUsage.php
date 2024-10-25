<?php declare(strict_types=1);








namespace PHPUnit\Event\Telemetry;

/**
@psalm-immutable
@no-named-arguments

*/
final class MemoryUsage
{
private readonly int $bytes;

public static function fromBytes(int $bytes): self
{
return new self($bytes);
}

private function __construct(int $bytes)
{
$this->bytes = $bytes;
}

public function bytes(): int
{
return $this->bytes;
}

public function diff(self $other): self
{
return self::fromBytes($this->bytes - $other->bytes);
}
}
