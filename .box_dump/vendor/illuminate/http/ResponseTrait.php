<?php

namespace Illuminate\Http;

use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\HeaderBag;
use Throwable;

trait ResponseTrait
{





public $original;






public $exception;






public function status()
{
return $this->getStatusCode();
}






public function statusText()
{
return $this->statusText;
}






public function content()
{
return $this->getContent();
}






public function getOriginalContent()
{
$original = $this->original;

return $original instanceof self ? $original->{__FUNCTION__}() : $original;
}









public function header($key, $values, $replace = true)
{
$this->headers->set($key, $values, $replace);

return $this;
}







public function withHeaders($headers)
{
if ($headers instanceof HeaderBag) {
$headers = $headers->all();
}

foreach ($headers as $key => $value) {
$this->headers->set($key, $value);
}

return $this;
}







public function cookie($cookie)
{
return $this->withCookie(...func_get_args());
}







public function withCookie($cookie)
{
if (is_string($cookie) && function_exists('cookie')) {
$cookie = cookie(...func_get_args());
}

$this->headers->setCookie($cookie);

return $this;
}









public function withoutCookie($cookie, $path = null, $domain = null)
{
if (is_string($cookie) && function_exists('cookie')) {
$cookie = cookie($cookie, null, -2628000, $path, $domain);
}

$this->headers->setCookie($cookie);

return $this;
}






public function getCallback()
{
return $this->callback ?? null;
}







public function withException(Throwable $e)
{
$this->exception = $e;

return $this;
}








public function throwResponse()
{
throw new HttpResponseException($this);
}
}
