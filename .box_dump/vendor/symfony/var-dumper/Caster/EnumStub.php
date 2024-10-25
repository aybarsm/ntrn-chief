<?php










namespace Symfony\Component\VarDumper\Caster;

use Symfony\Component\VarDumper\Cloner\Stub;






class EnumStub extends Stub
{
public bool $dumpKeys = true;

public function __construct(array $values, bool $dumpKeys = true)
{
$this->value = $values;
$this->dumpKeys = $dumpKeys;
}
}
