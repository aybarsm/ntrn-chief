<?php










namespace Symfony\Component\VarDumper\Caster;

use Symfony\Component\VarDumper\Cloner\Stub;






class TraceStub extends Stub
{
public bool $keepArgs;
public int $sliceOffset;
public ?int $sliceLength;
public int $numberingOffset;

public function __construct(array $trace, bool $keepArgs = true, int $sliceOffset = 0, ?int $sliceLength = null, int $numberingOffset = 0)
{
$this->value = $trace;
$this->keepArgs = $keepArgs;
$this->sliceOffset = $sliceOffset;
$this->sliceLength = $sliceLength;
$this->numberingOffset = $numberingOffset;
}
}
