<?php

namespace Illuminate\Http\Middleware;

use Closure;
use Illuminate\Http\Exceptions\PostTooLargeException;

class ValidatePostSize
{









public function handle($request, Closure $next)
{
$max = $this->getPostMaxSize();

if ($max > 0 && $request->server('CONTENT_LENGTH') > $max) {
throw new PostTooLargeException;
}

return $next($request);
}






protected function getPostMaxSize()
{
if (is_numeric($postMaxSize = ini_get('post_max_size'))) {
return (int) $postMaxSize;
}

$metric = strtoupper(substr($postMaxSize, -1));

$postMaxSize = (int) $postMaxSize;

return match ($metric) {
'K' => $postMaxSize * 1024,
'M' => $postMaxSize * 1048576,
'G' => $postMaxSize * 1073741824,
default => $postMaxSize,
};
}
}
