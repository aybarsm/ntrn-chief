<?php










namespace Symfony\Component\VarDumper\Caster;






class FrameStub extends EnumStub
{
public bool $keepArgs;
public bool $inTraceStub;

public function __construct(array $frame, bool $keepArgs = true, bool $inTraceStub = false)
{
$this->value = $frame;
$this->keepArgs = $keepArgs;
$this->inTraceStub = $inTraceStub;
}
}
