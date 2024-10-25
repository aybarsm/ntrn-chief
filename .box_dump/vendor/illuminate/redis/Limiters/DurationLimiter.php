<?php

namespace Illuminate\Redis\Limiters;

use Illuminate\Contracts\Redis\LimiterTimeoutException;
use Illuminate\Support\Sleep;

class DurationLimiter
{





private $redis;






private $name;






private $maxLocks;






private $decay;






public $decaysAt;






public $remaining;










public function __construct($redis, $name, $maxLocks, $decay)
{
$this->name = $name;
$this->decay = $decay;
$this->redis = $redis;
$this->maxLocks = $maxLocks;
}











public function block($timeout, $callback = null, $sleep = 750)
{
$starting = time();

while (! $this->acquire()) {
if (time() - $timeout >= $starting) {
throw new LimiterTimeoutException;
}

Sleep::usleep($sleep * 1000);
}

if (is_callable($callback)) {
return $callback();
}

return true;
}






public function acquire()
{
$results = $this->redis->eval(
$this->luaScript(), 1, $this->name, microtime(true), time(), $this->decay, $this->maxLocks
);

$this->decaysAt = $results[1];

$this->remaining = max(0, $results[2]);

return (bool) $results[0];
}






public function tooManyAttempts()
{
[$this->decaysAt, $this->remaining] = $this->redis->eval(
$this->tooManyAttemptsLuaScript(), 1, $this->name, microtime(true), time(), $this->decay, $this->maxLocks
);

return $this->remaining <= 0;
}






public function clear()
{
$this->redis->del($this->name);
}












protected function luaScript()
{
return <<<'LUA'
local function reset()
    redis.call('HMSET', KEYS[1], 'start', ARGV[2], 'end', ARGV[2] + ARGV[3], 'count', 1)
    return redis.call('EXPIRE', KEYS[1], ARGV[3] * 2)
end

if redis.call('EXISTS', KEYS[1]) == 0 then
    return {reset(), ARGV[2] + ARGV[3], ARGV[4] - 1}
end

if ARGV[1] >= redis.call('HGET', KEYS[1], 'start') and ARGV[1] <= redis.call('HGET', KEYS[1], 'end') then
    return {
        tonumber(redis.call('HINCRBY', KEYS[1], 'count', 1)) <= tonumber(ARGV[4]),
        redis.call('HGET', KEYS[1], 'end'),
        ARGV[4] - redis.call('HGET', KEYS[1], 'count')
    }
end

return {reset(), ARGV[2] + ARGV[3], ARGV[4] - 1}
LUA;
}












protected function tooManyAttemptsLuaScript()
{
return <<<'LUA'

if redis.call('EXISTS', KEYS[1]) == 0 then
    return {0, ARGV[2] + ARGV[3]}
end

if ARGV[1] >= redis.call('HGET', KEYS[1], 'start') and ARGV[1] <= redis.call('HGET', KEYS[1], 'end') then
    return {
        redis.call('HGET', KEYS[1], 'end'),
        ARGV[4] - redis.call('HGET', KEYS[1], 'count')
    }
end

return {0, ARGV[2] + ARGV[3]}
LUA;
}
}
