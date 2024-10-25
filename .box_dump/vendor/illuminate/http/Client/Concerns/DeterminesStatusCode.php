<?php

namespace Illuminate\Http\Client\Concerns;

trait DeterminesStatusCode
{





public function ok()
{
return $this->status() === 200;
}






public function created()
{
return $this->status() === 201;
}






public function accepted()
{
return $this->status() === 202;
}







public function noContent($status = 204)
{
return $this->status() === $status && $this->body() === '';
}






public function movedPermanently()
{
return $this->status() === 301;
}






public function found()
{
return $this->status() === 302;
}






public function notModified()
{
return $this->status() === 304;
}






public function badRequest()
{
return $this->status() === 400;
}






public function unauthorized()
{
return $this->status() === 401;
}






public function paymentRequired()
{
return $this->status() === 402;
}






public function forbidden()
{
return $this->status() === 403;
}






public function notFound()
{
return $this->status() === 404;
}






public function requestTimeout()
{
return $this->status() === 408;
}






public function conflict()
{
return $this->status() === 409;
}






public function unprocessableContent()
{
return $this->status() === 422;
}






public function unprocessableEntity()
{
return $this->unprocessableContent();
}






public function tooManyRequests()
{
return $this->status() === 429;
}
}
