<?php

namespace Illuminate\Foundation\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\ParameterBag;

class TransformsRequest
{







public function handle($request, Closure $next)
{
$this->clean($request);

return $next($request);
}







protected function clean($request)
{
$this->cleanParameterBag($request->query);

if ($request->isJson()) {
$this->cleanParameterBag($request->json());
} elseif ($request->request !== $request->query) {
$this->cleanParameterBag($request->request);
}
}







protected function cleanParameterBag(ParameterBag $bag)
{
$bag->replace($this->cleanArray($bag->all()));
}








protected function cleanArray(array $data, $keyPrefix = '')
{
foreach ($data as $key => $value) {
$data[$key] = $this->cleanValue($keyPrefix.$key, $value);
}

return $data;
}








protected function cleanValue($key, $value)
{
if (is_array($value)) {
return $this->cleanArray($value, $key.'.');
}

return $this->transform($key, $value);
}








protected function transform($key, $value)
{
return $value;
}
}
