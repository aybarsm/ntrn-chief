<?php

namespace Illuminate\Database\Eloquent;

class SoftDeletingScope implements Scope
{





protected $extensions = ['Restore', 'RestoreOrCreate', 'CreateOrRestore', 'WithTrashed', 'WithoutTrashed', 'OnlyTrashed'];

/**
@template






*/
public function apply(Builder $builder, Model $model)
{
$builder->whereNull($model->getQualifiedDeletedAtColumn());
}







public function extend(Builder $builder)
{
foreach ($this->extensions as $extension) {
$this->{"add{$extension}"}($builder);
}

$builder->onDelete(function (Builder $builder) {
$column = $this->getDeletedAtColumn($builder);

return $builder->update([
$column => $builder->getModel()->freshTimestampString(),
]);
});
}







protected function getDeletedAtColumn(Builder $builder)
{
if (count((array) $builder->getQuery()->joins) > 0) {
return $builder->getModel()->getQualifiedDeletedAtColumn();
}

return $builder->getModel()->getDeletedAtColumn();
}







protected function addRestore(Builder $builder)
{
$builder->macro('restore', function (Builder $builder) {
$builder->withTrashed();

return $builder->update([$builder->getModel()->getDeletedAtColumn() => null]);
});
}







protected function addRestoreOrCreate(Builder $builder)
{
$builder->macro('restoreOrCreate', function (Builder $builder, array $attributes = [], array $values = []) {
$builder->withTrashed();

return tap($builder->firstOrCreate($attributes, $values), function ($instance) {
$instance->restore();
});
});
}







protected function addCreateOrRestore(Builder $builder)
{
$builder->macro('createOrRestore', function (Builder $builder, array $attributes = [], array $values = []) {
$builder->withTrashed();

return tap($builder->createOrFirst($attributes, $values), function ($instance) {
$instance->restore();
});
});
}







protected function addWithTrashed(Builder $builder)
{
$builder->macro('withTrashed', function (Builder $builder, $withTrashed = true) {
if (! $withTrashed) {
return $builder->withoutTrashed();
}

return $builder->withoutGlobalScope($this);
});
}







protected function addWithoutTrashed(Builder $builder)
{
$builder->macro('withoutTrashed', function (Builder $builder) {
$model = $builder->getModel();

$builder->withoutGlobalScope($this)->whereNull(
$model->getQualifiedDeletedAtColumn()
);

return $builder;
});
}







protected function addOnlyTrashed(Builder $builder)
{
$builder->macro('onlyTrashed', function (Builder $builder) {
$model = $builder->getModel();

$builder->withoutGlobalScope($this)->whereNotNull(
$model->getQualifiedDeletedAtColumn()
);

return $builder;
});
}
}
