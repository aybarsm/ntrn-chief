<?php

namespace Illuminate\Database\Concerns;

trait ParsesSearchPath
{






protected function parseSearchPath($searchPath)
{
if (is_string($searchPath)) {
preg_match_all('/[^\s,"\']+/', $searchPath, $matches);

$searchPath = $matches[0];
}

return array_map(function ($schema) {
return trim($schema, '\'"');
}, $searchPath ?? []);
}
}
