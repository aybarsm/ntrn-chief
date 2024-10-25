<?php

namespace Illuminate\Support\Facades;

use Illuminate\Queue\Worker;
use Illuminate\Support\Testing\Fakes\QueueFake;





















































class Queue extends Facade
{







public static function popUsing($workerName, $callback)
{
Worker::popUsing($workerName, $callback);
}







public static function fake($jobsToFake = [])
{
$actualQueueManager = static::isFake()
? static::getFacadeRoot()->queue
: static::getFacadeRoot();

return tap(new QueueFake(static::getFacadeApplication(), $jobsToFake, $actualQueueManager), function ($fake) {
static::swap($fake);
});
}






protected static function getFacadeAccessor()
{
return 'queue';
}
}
