<?php

namespace Illuminate\View\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\LazyCollection;

trait ManagesLoops
{





protected $loopsStack = [];







public function addLoop($data)
{
$length = is_countable($data) && ! $data instanceof LazyCollection
? count($data)
: null;

$parent = Arr::last($this->loopsStack);

$this->loopsStack[] = [
'iteration' => 0,
'index' => 0,
'remaining' => $length ?? null,
'count' => $length,
'first' => true,
'last' => isset($length) ? $length == 1 : null,
'odd' => false,
'even' => true,
'depth' => count($this->loopsStack) + 1,
'parent' => $parent ? (object) $parent : null,
];
}






public function incrementLoopIndices()
{
$loop = $this->loopsStack[$index = count($this->loopsStack) - 1];

$this->loopsStack[$index] = array_merge($this->loopsStack[$index], [
'iteration' => $loop['iteration'] + 1,
'index' => $loop['iteration'],
'first' => $loop['iteration'] == 0,
'odd' => ! $loop['odd'],
'even' => ! $loop['even'],
'remaining' => isset($loop['count']) ? $loop['remaining'] - 1 : null,
'last' => isset($loop['count']) ? $loop['iteration'] == $loop['count'] - 1 : null,
]);
}






public function popLoop()
{
array_pop($this->loopsStack);
}






public function getLastLoop()
{
if ($last = Arr::last($this->loopsStack)) {
return (object) $last;
}
}






public function getLoopStack()
{
return $this->loopsStack;
}
}
