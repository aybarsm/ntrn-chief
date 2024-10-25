<?php










namespace Symfony\Component\VarDumper\Caster;

use Symfony\Component\VarDumper\Cloner\Stub;






final class FiberCaster
{
public static function castFiber(\Fiber $fiber, array $a, Stub $stub, bool $isNested, int $filter = 0): array
{
$prefix = Caster::PREFIX_VIRTUAL;

if ($fiber->isTerminated()) {
$status = 'terminated';
} elseif ($fiber->isRunning()) {
$status = 'running';
} elseif ($fiber->isSuspended()) {
$status = 'suspended';
} elseif ($fiber->isStarted()) {
$status = 'started';
} else {
$status = 'not started';
}

$a[$prefix.'status'] = $status;

return $a;
}
}
