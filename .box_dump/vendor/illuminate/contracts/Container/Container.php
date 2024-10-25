<?php

namespace Illuminate\Contracts\Container;

use Closure;
use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface
{






public function bound($abstract);










public function alias($abstract, $alias);








public function tag($abstracts, $tags);







public function tagged($tag);









public function bind($abstract, $concrete = null, $shared = false);








public function bindMethod($method, $callback);









public function bindIf($abstract, $concrete = null, $shared = false);








public function singleton($abstract, $concrete = null);








public function singletonIf($abstract, $concrete = null);








public function scoped($abstract, $concrete = null);








public function scopedIf($abstract, $concrete = null);










public function extend($abstract, Closure $closure);








public function instance($abstract, $instance);









public function addContextualBinding($concrete, $abstract, $implementation);







public function when($concrete);







public function factory($abstract);






public function flush();










public function make($abstract, array $parameters = []);









public function call($callback, array $parameters = [], $defaultMethod = null);







public function resolved($abstract);








public function beforeResolving($abstract, ?Closure $callback = null);








public function resolving($abstract, ?Closure $callback = null);








public function afterResolving($abstract, ?Closure $callback = null);
}
