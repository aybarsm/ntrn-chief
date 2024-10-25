<?php

namespace Illuminate\Redis\Connections;

use Closure;
use Illuminate\Contracts\Redis\Connection as ConnectionContract;
use RedisException;

/**
@mixin
*/
class PhpRedisConnection extends Connection implements ConnectionContract
{
use PacksPhpRedisValues;






protected $connector;






protected $config;









public function __construct($client, ?callable $connector = null, array $config = [])
{
$this->client = $client;
$this->config = $config;
$this->connector = $connector;
}







public function get($key)
{
$result = $this->command('get', [$key]);

return $result !== false ? $result : null;
}







public function mget(array $keys)
{
return array_map(function ($value) {
return $value !== false ? $value : null;
}, $this->command('mget', [$keys]));
}











public function set($key, $value, $expireResolution = null, $expireTTL = null, $flag = null)
{
return $this->command('set', [
$key,
$value,
$expireResolution ? [$flag, $expireResolution => $expireTTL] : null,
]);
}








public function setnx($key, $value)
{
return (int) $this->command('setnx', [$key, $value]);
}








public function hmget($key, ...$dictionary)
{
if (count($dictionary) === 1) {
$dictionary = $dictionary[0];
}

return array_values($this->command('hmget', [$key, $dictionary]));
}








public function hmset($key, ...$dictionary)
{
if (count($dictionary) === 1) {
$dictionary = $dictionary[0];
} else {
$input = collect($dictionary);

$dictionary = $input->nth(2)->combine($input->nth(2, 1))->toArray();
}

return $this->command('hmset', [$key, $dictionary]);
}









public function hsetnx($hash, $key, $value)
{
return (int) $this->command('hsetnx', [$hash, $key, $value]);
}









public function lrem($key, $count, $value)
{
return $this->command('lrem', [$key, $value, $count]);
}







public function blpop(...$arguments)
{
$result = $this->command('blpop', $arguments);

return empty($result) ? null : $result;
}







public function brpop(...$arguments)
{
$result = $this->command('brpop', $arguments);

return empty($result) ? null : $result;
}








public function spop($key, $count = 1)
{
return $this->command('spop', func_get_args());
}








public function zadd($key, ...$dictionary)
{
if (is_array(end($dictionary))) {
foreach (array_pop($dictionary) as $member => $score) {
$dictionary[] = $score;
$dictionary[] = $member;
}
}

$options = [];

foreach (array_slice($dictionary, 0, 3) as $i => $value) {
if (in_array($value, ['nx', 'xx', 'ch', 'incr', 'gt', 'lt', 'NX', 'XX', 'CH', 'INCR', 'GT', 'LT'], true)) {
$options[] = $value;

unset($dictionary[$i]);
}
}

return $this->command('zadd', array_merge([$key], [$options], array_values($dictionary)));
}










public function zrangebyscore($key, $min, $max, $options = [])
{
if (isset($options['limit']) && ! array_is_list($options['limit'])) {
$options['limit'] = [
$options['limit']['offset'],
$options['limit']['count'],
];
}

return $this->command('zRangeByScore', [$key, $min, $max, $options]);
}










public function zrevrangebyscore($key, $min, $max, $options = [])
{
if (isset($options['limit']) && ! array_is_list($options['limit'])) {
$options['limit'] = [
$options['limit']['offset'],
$options['limit']['count'],
];
}

return $this->command('zRevRangeByScore', [$key, $min, $max, $options]);
}









public function zinterstore($output, $keys, $options = [])
{
return $this->command('zinterstore', [$output, $keys,
$options['weights'] ?? null,
$options['aggregate'] ?? 'sum',
]);
}









public function zunionstore($output, $keys, $options = [])
{
return $this->command('zunionstore', [$output, $keys,
$options['weights'] ?? null,
$options['aggregate'] ?? 'sum',
]);
}








public function scan($cursor, $options = [])
{
$result = $this->client->scan($cursor,
$options['match'] ?? '*',
$options['count'] ?? 10
);

if ($result === false) {
$result = [];
}

return $cursor === 0 && empty($result) ? false : [$cursor, $result];
}









public function zscan($key, $cursor, $options = [])
{
$result = $this->client->zscan($key, $cursor,
$options['match'] ?? '*',
$options['count'] ?? 10
);

if ($result === false) {
$result = [];
}

return $cursor === 0 && empty($result) ? false : [$cursor, $result];
}









public function hscan($key, $cursor, $options = [])
{
$result = $this->client->hscan($key, $cursor,
$options['match'] ?? '*',
$options['count'] ?? 10
);

if ($result === false) {
$result = [];
}

return $cursor === 0 && empty($result) ? false : [$cursor, $result];
}









public function sscan($key, $cursor, $options = [])
{
$result = $this->client->sscan($key, $cursor,
$options['match'] ?? '*',
$options['count'] ?? 10
);

if ($result === false) {
$result = [];
}

return $cursor === 0 && empty($result) ? false : [$cursor, $result];
}







public function pipeline(?callable $callback = null)
{
$pipeline = $this->client()->pipeline();

return is_null($callback)
? $pipeline
: tap($pipeline, $callback)->exec();
}







public function transaction(?callable $callback = null)
{
$transaction = $this->client()->multi();

return is_null($callback)
? $transaction
: tap($transaction, $callback)->exec();
}









public function evalsha($script, $numkeys, ...$arguments)
{
return $this->command('evalsha', [
$this->script('load', $script), $arguments, $numkeys,
]);
}









public function eval($script, $numberOfKeys, ...$arguments)
{
return $this->command('eval', [$script, $arguments, $numberOfKeys]);
}








public function subscribe($channels, Closure $callback)
{
$this->client->subscribe((array) $channels, function ($redis, $channel, $message) use ($callback) {
$callback($message, $channel);
});
}








public function psubscribe($channels, Closure $callback)
{
$this->client->psubscribe((array) $channels, function ($redis, $pattern, $channel, $message) use ($callback) {
$callback($message, $channel);
});
}









public function createSubscription($channels, Closure $callback, $method = 'subscribe')
{

}






public function flushdb()
{
$arguments = func_get_args();

if (strtoupper((string) ($arguments[0] ?? null)) === 'ASYNC') {
return $this->command('flushdb', [true]);
}

return $this->command('flushdb');
}







public function executeRaw(array $parameters)
{
return $this->command('rawCommand', $parameters);
}










public function command($method, array $parameters = [])
{
try {
return parent::command($method, $parameters);
} catch (RedisException $e) {
foreach (['went away', 'socket', 'read error on connection', 'Connection lost'] as $errorMessage) {
if (str_contains($e->getMessage(), $errorMessage)) {
$this->client = $this->connector ? call_user_func($this->connector) : $this->client;

break;
}
}

throw $e;
}
}






public function disconnect()
{
$this->client->close();
}








public function __call($method, $parameters)
{
return parent::__call(strtolower($method), $parameters);
}
}
