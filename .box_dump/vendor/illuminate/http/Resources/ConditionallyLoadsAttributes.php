<?php

namespace Illuminate\Http\Resources;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait ConditionallyLoadsAttributes
{






protected function filter($data)
{
$index = -1;

foreach ($data as $key => $value) {
$index++;

if (is_array($value)) {
$data[$key] = $this->filter($value);

continue;
}

if (is_numeric($key) && $value instanceof MergeValue) {
return $this->mergeData(
$data, $index, $this->filter($value->data),
array_values($value->data) === $value->data
);
}

if ($value instanceof self && is_null($value->resource)) {
$data[$key] = null;
}
}

return $this->removeMissingValues($data);
}










protected function mergeData($data, $index, $merge, $numericKeys)
{
if ($numericKeys) {
return $this->removeMissingValues(array_merge(
array_merge(array_slice($data, 0, $index, true), $merge),
$this->filter(array_values(array_slice($data, $index + 1, null, true)))
));
}

return $this->removeMissingValues(array_slice($data, 0, $index, true) +
$merge +
$this->filter(array_slice($data, $index + 1, null, true)));
}







protected function removeMissingValues($data)
{
$numericKeys = true;

foreach ($data as $key => $value) {
if (($value instanceof PotentiallyMissing && $value->isMissing()) ||
($value instanceof self &&
$value->resource instanceof PotentiallyMissing &&
$value->isMissing())) {
unset($data[$key]);
} else {
$numericKeys = $numericKeys && is_numeric($key);
}
}

if (property_exists($this, 'preserveKeys') && $this->preserveKeys === true) {
return $data;
}

return $numericKeys ? array_values($data) : $data;
}









protected function when($condition, $value, $default = null)
{
if ($condition) {
return value($value);
}

return func_num_args() === 3 ? value($default) : new MissingValue;
}









public function unless($condition, $value, $default = null)
{
$arguments = func_num_args() === 2 ? [$value] : [$value, $default];

return $this->when(! $condition, ...$arguments);
}







protected function merge($value)
{
return $this->mergeWhen(true, $value);
}









protected function mergeWhen($condition, $value, $default = null)
{
if ($condition) {
return new MergeValue(value($value));
}

return func_num_args() === 3 ? new MergeValue(value($default)) : new MissingValue();
}









protected function mergeUnless($condition, $value, $default = null)
{
$arguments = func_num_args() === 2 ? [$value] : [$value, $default];

return $this->mergeWhen(! $condition, ...$arguments);
}







protected function attributes($attributes)
{
return new MergeValue(
Arr::only($this->resource->toArray(), $attributes)
);
}









public function whenHas($attribute, $value = null, $default = null)
{
if (func_num_args() < 3) {
$default = new MissingValue;
}

if (! array_key_exists($attribute, $this->resource->getAttributes())) {
return value($default);
}

return func_num_args() === 1
? $this->resource->{$attribute}
: value($value, $this->resource->{$attribute});
}








protected function whenNull($value, $default = null)
{
$arguments = func_num_args() == 1 ? [$value] : [$value, $default];

return $this->when(is_null($value), ...$arguments);
}








protected function whenNotNull($value, $default = null)
{
$arguments = func_num_args() == 1 ? [$value] : [$value, $default];

return $this->when(! is_null($value), ...$arguments);
}









protected function whenAppended($attribute, $value = null, $default = null)
{
if ($this->resource->hasAppended($attribute)) {
return func_num_args() >= 2 ? value($value) : $this->resource->$attribute;
}

return func_num_args() === 3 ? value($default) : new MissingValue;
}









protected function whenLoaded($relationship, $value = null, $default = null)
{
if (func_num_args() < 3) {
$default = new MissingValue;
}

if (! $this->resource->relationLoaded($relationship)) {
return value($default);
}

$loadedValue = $this->resource->{$relationship};

if (func_num_args() === 1) {
return $loadedValue;
}

if ($loadedValue === null) {
return;
}

if ($value === null) {
$value = value(...);
}

return value($value, $loadedValue);
}









public function whenCounted($relationship, $value = null, $default = null)
{
if (func_num_args() < 3) {
$default = new MissingValue;
}

$attribute = (string) Str::of($relationship)->snake()->finish('_count');

if (! array_key_exists($attribute, $this->resource->getAttributes())) {
return value($default);
}

if (func_num_args() === 1) {
return $this->resource->{$attribute};
}

if ($this->resource->{$attribute} === null) {
return;
}

if ($value === null) {
$value = value(...);
}

return value($value, $this->resource->{$attribute});
}











public function whenAggregated($relationship, $column, $aggregate, $value = null, $default = null)
{
if (func_num_args() < 5) {
$default = new MissingValue;
}

$attribute = (string) Str::of($relationship)->snake()->append('_')->append($aggregate)->append('_')->finish($column);

if (! array_key_exists($attribute, $this->resource->getAttributes())) {
return value($default);
}

if (func_num_args() === 3) {
return $this->resource->{$attribute};
}

if ($this->resource->{$attribute} === null) {
return;
}

if ($value === null) {
$value = value(...);
}

return value($value, $this->resource->{$attribute});
}









public function whenExistsLoaded($relationship, $value = null, $default = null)
{
if (func_num_args() < 3) {
$default = new MissingValue;
}

$attribute = (string) Str::of($relationship)->snake()->finish('_exists');

if (! array_key_exists($attribute, $this->resource->getAttributes())) {
return value($default);
}

if (func_num_args() === 1) {
return $this->resource->{$attribute};
}

if ($this->resource->{$attribute} === null) {
return;
}

return value($value, $this->resource->{$attribute});
}









protected function whenPivotLoaded($table, $value, $default = null)
{
return $this->whenPivotLoadedAs('pivot', ...func_get_args());
}










protected function whenPivotLoadedAs($accessor, $table, $value, $default = null)
{
if (func_num_args() === 3) {
$default = new MissingValue;
}

return $this->when(
$this->hasPivotLoadedAs($accessor, $table),
...[$value, $default]
);
}







protected function hasPivotLoaded($table)
{
return $this->hasPivotLoadedAs('pivot', $table);
}








protected function hasPivotLoadedAs($accessor, $table)
{
return isset($this->resource->$accessor) &&
($this->resource->$accessor instanceof $table ||
$this->resource->$accessor->getTable() === $table);
}









protected function transform($value, callable $callback, $default = null)
{
return transform(
$value, $callback, func_num_args() === 3 ? $default : new MissingValue
);
}
}
