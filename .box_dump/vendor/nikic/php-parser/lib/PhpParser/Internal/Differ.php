<?php declare(strict_types=1);

namespace PhpParser\Internal;

/**
@template






*/
class Differ {

private $isEqual;






public function __construct(callable $isEqual) {
$this->isEqual = $isEqual;
}









public function diff(array $old, array $new): array {
$old = \array_values($old);
$new = \array_values($new);
list($trace, $x, $y) = $this->calculateTrace($old, $new);
return $this->extractDiff($trace, $x, $y, $old, $new);
}












public function diffWithReplacements(array $old, array $new): array {
return $this->coalesceReplacements($this->diff($old, $new));
}






private function calculateTrace(array $old, array $new): array {
$n = \count($old);
$m = \count($new);
$max = $n + $m;
$v = [1 => 0];
$trace = [];
for ($d = 0; $d <= $max; $d++) {
$trace[] = $v;
for ($k = -$d; $k <= $d; $k += 2) {
if ($k === -$d || ($k !== $d && $v[$k - 1] < $v[$k + 1])) {
$x = $v[$k + 1];
} else {
$x = $v[$k - 1] + 1;
}

$y = $x - $k;
while ($x < $n && $y < $m && ($this->isEqual)($old[$x], $new[$y])) {
$x++;
$y++;
}

$v[$k] = $x;
if ($x >= $n && $y >= $m) {
return [$trace, $x, $y];
}
}
}
throw new \Exception('Should not happen');
}







private function extractDiff(array $trace, int $x, int $y, array $old, array $new): array {
$result = [];
for ($d = \count($trace) - 1; $d >= 0; $d--) {
$v = $trace[$d];
$k = $x - $y;

if ($k === -$d || ($k !== $d && $v[$k - 1] < $v[$k + 1])) {
$prevK = $k + 1;
} else {
$prevK = $k - 1;
}

$prevX = $v[$prevK];
$prevY = $prevX - $prevK;

while ($x > $prevX && $y > $prevY) {
$result[] = new DiffElem(DiffElem::TYPE_KEEP, $old[$x - 1], $new[$y - 1]);
$x--;
$y--;
}

if ($d === 0) {
break;
}

while ($x > $prevX) {
$result[] = new DiffElem(DiffElem::TYPE_REMOVE, $old[$x - 1], null);
$x--;
}

while ($y > $prevY) {
$result[] = new DiffElem(DiffElem::TYPE_ADD, null, $new[$y - 1]);
$y--;
}
}
return array_reverse($result);
}







private function coalesceReplacements(array $diff): array {
$newDiff = [];
$c = \count($diff);
for ($i = 0; $i < $c; $i++) {
$diffType = $diff[$i]->type;
if ($diffType !== DiffElem::TYPE_REMOVE) {
$newDiff[] = $diff[$i];
continue;
}

$j = $i;
while ($j < $c && $diff[$j]->type === DiffElem::TYPE_REMOVE) {
$j++;
}

$k = $j;
while ($k < $c && $diff[$k]->type === DiffElem::TYPE_ADD) {
$k++;
}

if ($j - $i === $k - $j) {
$len = $j - $i;
for ($n = 0; $n < $len; $n++) {
$newDiff[] = new DiffElem(
DiffElem::TYPE_REPLACE, $diff[$i + $n]->old, $diff[$j + $n]->new
);
}
} else {
for (; $i < $k; $i++) {
$newDiff[] = $diff[$i];
}
}
$i = $k - 1;
}
return $newDiff;
}
}
