<?php

namespace Illuminate\Redis\Limiters;

use Illuminate\Contracts\Redis\LimiterTimeoutException;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Throwable;

class ConcurrencyLimiter
{





protected $redis;






protected $name;






protected $maxLocks;






protected $releaseAfter;










public function __construct($redis, $name, $maxLocks, $releaseAfter)
{
$this->name = $name;
$this->redis = $redis;
$this->maxLocks = $maxLocks;
$this->releaseAfter = $releaseAfter;
}












public function block($timeout, $callback = null, $sleep = 250)
{
$starting = time();

$id = Str::random(20);

while (! $slot = $this->acquire($id)) {
if (time() - $timeout >= $starting) {
throw new LimiterTimeoutException;
}

Sleep::usleep($sleep * 1000);
}

if (is_callable($callback)) {
try {
return tap($callback(), function () use ($slot, $id) {
$this->release($slot, $id);
});
} catch (Throwable $exception) {
$this->release($slot, $id);

throw $exception;
}
}

return true;
}







protected function acquire($id)
{
$slots = array_map(function ($i) {
return $this->name.$i;
}, range(1, $this->maxLocks));

return $this->redis->eval(...array_merge(
[$this->lockScript(), count($slots)],
array_merge($slots, [$this->name, $this->releaseAfter, $id])
));
}











protected function lockScript()
{
return <<<'LUA'
for index, value in pairs(redis.call('mget', unpack(KEYS))) do
    if not value then
        redis.call('set', KEYS[index], ARGV[3], "EX", ARGV[2])
        return ARGV[1]..index
    end
end
LUA;
}








protected function release($key, $id)
{
$this->redis->eval($this->releaseScript(), 1, $key, $id);
}









protected function releaseScript()
{
return <<<'LUA'
if redis.call('get', KEYS[1]) == ARGV[1]
then
    return redis.call('del', KEYS[1])
else
    return 0
end
LUA;
}
}
