<?php

namespace Illuminate\Events;

class InvokeQueuedClosure
{







public function handle($closure, array $arguments)
{
call_user_func($closure->getClosure(), ...$arguments);
}










public function failed($closure, array $arguments, array $catchCallbacks, $exception)
{
$arguments[] = $exception;

collect($catchCallbacks)->each->__invoke(...$arguments);
}
}
