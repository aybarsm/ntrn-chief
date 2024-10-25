<?php










namespace Symfony\Component\HttpKernel\DataCollector;

use Symfony\Component\VarDumper\Caster\CutStub;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\ClonerInterface;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Cloner\Stub;
use Symfony\Component\VarDumper\Cloner\VarCloner;









abstract class DataCollector implements DataCollectorInterface
{
protected array|Data $data = [];

private ClonerInterface $cloner;







protected function cloneVar(mixed $var): Data
{
if ($var instanceof Data) {
return $var;
}
if (!isset($this->cloner)) {
$this->cloner = new VarCloner();
$this->cloner->setMaxItems(-1);
$this->cloner->addCasters($this->getCasters());
}

return $this->cloner->cloneVar($var);
}




protected function getCasters(): array
{
$casters = [
'*' => function ($v, array $a, Stub $s, $isNested) {
if (!$v instanceof Stub) {
$b = $a;
foreach ($a as $k => $v) {
if (!\is_object($v) || $v instanceof \DateTimeInterface || $v instanceof Stub) {
continue;
}

try {
$a[$k] = $s = new CutStub($v);

if ($b[$k] === $s) {

$a[$k] = $v;
}
} catch (\TypeError $e) {

}
}
}

return $a;
},
] + ReflectionCaster::UNSET_CLOSURE_FILE_INFO;

return $casters;
}

public function __sleep(): array
{
return ['data'];
}

public function __wakeup(): void
{
}




final protected function serialize(): void
{
}




final protected function unserialize(string $data): void
{
}




public function reset()
{
$this->data = [];
}
}
