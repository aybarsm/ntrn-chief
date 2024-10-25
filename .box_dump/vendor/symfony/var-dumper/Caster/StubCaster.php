<?php










namespace Symfony\Component\VarDumper\Caster;

use Symfony\Component\VarDumper\Cloner\Stub;








class StubCaster
{
public static function castStub(Stub $c, array $a, Stub $stub, bool $isNested): array
{
if ($isNested) {
$stub->type = $c->type;
$stub->class = $c->class;
$stub->value = $c->value;
$stub->handle = $c->handle;
$stub->cut = $c->cut;
$stub->attr = $c->attr;

if (Stub::TYPE_REF === $c->type && !$c->class && \is_string($c->value) && !preg_match('//u', $c->value)) {
$stub->type = Stub::TYPE_STRING;
$stub->class = Stub::STRING_BINARY;
}

$a = [];
}

return $a;
}

public static function castCutArray(CutArrayStub $c, array $a, Stub $stub, bool $isNested): array
{
return $isNested ? $c->preservedSubset : $a;
}

public static function cutInternals($obj, array $a, Stub $stub, bool $isNested): array
{
if ($isNested) {
$stub->cut += \count($a);

return [];
}

return $a;
}

public static function castEnum(EnumStub $c, array $a, Stub $stub, bool $isNested): array
{
if ($isNested) {
$stub->class = $c->dumpKeys ? '' : null;
$stub->handle = 0;
$stub->value = null;
$stub->cut = $c->cut;
$stub->attr = $c->attr;

$a = [];

if ($c->value) {
foreach (array_keys($c->value) as $k) {
$keys[] = !isset($k[0]) || "\0" !== $k[0] ? Caster::PREFIX_VIRTUAL.$k : $k;
}

$a = array_combine($keys, $c->value);
}
}

return $a;
}

public static function castScalar(ScalarStub $scalarStub, array $a, Stub $stub): array
{
$stub->type = Stub::TYPE_SCALAR;
$stub->attr['value'] = $scalarStub->value;

return $a;
}
}
