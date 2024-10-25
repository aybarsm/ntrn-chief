<?php declare(strict_types=1);








namespace PHPUnit\Event\Telemetry;

use function sprintf;

/**
@psalm-immutable
@no-named-arguments

*/
final class Info
{
private readonly Snapshot $current;
private readonly Duration $durationSinceStart;
private readonly MemoryUsage $memorySinceStart;
private readonly Duration $durationSincePrevious;
private readonly MemoryUsage $memorySincePrevious;

public function __construct(Snapshot $current, Duration $durationSinceStart, MemoryUsage $memorySinceStart, Duration $durationSincePrevious, MemoryUsage $memorySincePrevious)
{
$this->current = $current;
$this->durationSinceStart = $durationSinceStart;
$this->memorySinceStart = $memorySinceStart;
$this->durationSincePrevious = $durationSincePrevious;
$this->memorySincePrevious = $memorySincePrevious;
}

public function time(): HRTime
{
return $this->current->time();
}

public function memoryUsage(): MemoryUsage
{
return $this->current->memoryUsage();
}

public function peakMemoryUsage(): MemoryUsage
{
return $this->current->peakMemoryUsage();
}

public function durationSinceStart(): Duration
{
return $this->durationSinceStart;
}

public function memoryUsageSinceStart(): MemoryUsage
{
return $this->memorySinceStart;
}

public function durationSincePrevious(): Duration
{
return $this->durationSincePrevious;
}

public function memoryUsageSincePrevious(): MemoryUsage
{
return $this->memorySincePrevious;
}

public function garbageCollectorStatus(): GarbageCollectorStatus
{
return $this->current->garbageCollectorStatus();
}

public function asString(): string
{
return sprintf(
'[%s / %s] [%d bytes]',
$this->durationSinceStart()->asString(),
$this->durationSincePrevious()->asString(),
$this->memoryUsage()->bytes(),
);
}
}
