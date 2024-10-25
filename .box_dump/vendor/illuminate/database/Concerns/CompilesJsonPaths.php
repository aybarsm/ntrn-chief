<?php

namespace Illuminate\Database\Concerns;

use Illuminate\Support\Str;

trait CompilesJsonPaths
{






protected function wrapJsonFieldAndPath($column)
{
$parts = explode('->', $column, 2);

$field = $this->wrap($parts[0]);

$path = count($parts) > 1 ? ', '.$this->wrapJsonPath($parts[1], '->') : '';

return [$field, $path];
}








protected function wrapJsonPath($value, $delimiter = '->')
{
$value = preg_replace("/([\\\\]+)?\\'/", "''", $value);

$jsonPath = collect(explode($delimiter, $value))
->map(fn ($segment) => $this->wrapJsonPathSegment($segment))
->join('.');

return "'$".(str_starts_with($jsonPath, '[') ? '' : '.').$jsonPath."'";
}







protected function wrapJsonPathSegment($segment)
{
if (preg_match('/(\[[^\]]+\])+$/', $segment, $parts)) {
$key = Str::beforeLast($segment, $parts[0]);

if (! empty($key)) {
return '"'.$key.'"'.$parts[0];
}

return $parts[0];
}

return '"'.$segment.'"';
}
}
