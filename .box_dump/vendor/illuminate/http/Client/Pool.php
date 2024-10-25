<?php

namespace Illuminate\Http\Client;

use GuzzleHttp\Utils;

/**
@mixin
*/
class Pool
{





protected $factory;






protected $handler;






protected $pool = [];







public function __construct(?Factory $factory = null)
{
$this->factory = $factory ?: new Factory();
$this->handler = Utils::chooseHandler();
}







public function as(string $key)
{
return $this->pool[$key] = $this->asyncRequest();
}






protected function asyncRequest()
{
return $this->factory->setHandler($this->handler)->async();
}






public function getRequests()
{
return $this->pool;
}








public function __call($method, $parameters)
{
return $this->pool[] = $this->asyncRequest()->$method(...$parameters);
}
}
