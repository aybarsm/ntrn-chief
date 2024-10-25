<?php










namespace Symfony\Component\VarDumper\Caster;

use Symfony\Component\VarDumper\Cloner\Stub;






class ConstStub extends Stub
{
public function __construct(string $name, string|int|float|null $value = null)
{
$this->class = $name;
$this->value = 1 < \func_num_args() ? $value : $name;
}

public function __toString(): string
{
return (string) $this->value;
}
}
