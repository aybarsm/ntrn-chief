<?php

namespace Illuminate\Database\Eloquent;

use Illuminate\Database\Events\ModelsPruned;
use LogicException;

trait Prunable
{






public function pruneAll(int $chunkSize = 1000)
{
$total = 0;

$this->prunable()
->when(in_array(SoftDeletes::class, class_uses_recursive(get_class($this))), function ($query) {
$query->withTrashed();
})->chunkById($chunkSize, function ($models) use (&$total) {
$models->each->prune();

$total += $models->count();

event(new ModelsPruned(static::class, $total));
});

return $total;
}






public function prunable()
{
throw new LogicException('Please implement the prunable method on your model.');
}






public function prune()
{
$this->pruning();

return in_array(SoftDeletes::class, class_uses_recursive(get_class($this)))
? $this->forceDelete()
: $this->delete();
}






protected function pruning()
{

}
}
