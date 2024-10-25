<?php

namespace Illuminate\Foundation\Bus;

use Closure;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Fluent;

trait Dispatchable
{






public static function dispatch(...$arguments)
{
return new PendingDispatch(new static(...$arguments));
}








public static function dispatchIf($boolean, ...$arguments)
{
if ($boolean instanceof Closure) {
$dispatchable = new static(...$arguments);

return value($boolean, $dispatchable)
? new PendingDispatch($dispatchable)
: new Fluent;
}

return value($boolean)
? new PendingDispatch(new static(...$arguments))
: new Fluent;
}








public static function dispatchUnless($boolean, ...$arguments)
{
if ($boolean instanceof Closure) {
$dispatchable = new static(...$arguments);

return ! value($boolean, $dispatchable)
? new PendingDispatch($dispatchable)
: new Fluent;
}

return ! value($boolean)
? new PendingDispatch(new static(...$arguments))
: new Fluent;
}









public static function dispatchSync(...$arguments)
{
return app(Dispatcher::class)->dispatchSync(new static(...$arguments));
}







public static function dispatchAfterResponse(...$arguments)
{
return self::dispatch(...$arguments)->afterResponse();
}







public static function withChain($chain)
{
return new PendingChain(static::class, $chain);
}
}
