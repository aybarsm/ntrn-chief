<?php

namespace Illuminate\Database\Eloquent;

use Illuminate\Database\Events\ModelsPruned;
use LogicException;

trait MassPrunable
{






public function pruneAll(int $chunkSize = 1000)
{
$query = tap($this->prunable(), function ($query) use ($chunkSize) {
$query->when(! $query->getQuery()->limit, function ($query) use ($chunkSize) {
$query->limit($chunkSize);
});
});

$total = 0;

do {
$total += $count = in_array(SoftDeletes::class, class_uses_recursive(get_class($this)))
? $query->forceDelete()
: $query->delete();

if ($count > 0) {
event(new ModelsPruned(static::class, $total));
}
} while ($count > 0);

return $total;
}






public function prunable()
{
throw new LogicException('Please implement the prunable method on your model.');
}
}
