<?php

namespace Illuminate\Redis\Limiters;

use Illuminate\Contracts\Redis\LimiterTimeoutException;
use Illuminate\Support\InteractsWithTime;

class ConcurrencyLimiterBuilder
{
use InteractsWithTime;






public $connection;






public $name;






public $maxLocks;






public $releaseAfter = 60;






public $timeout = 3;






public $sleep = 250;








public function __construct($connection, $name)
{
$this->name = $name;
$this->connection = $connection;
}







public function limit($maxLocks)
{
$this->maxLocks = $maxLocks;

return $this;
}







public function releaseAfter($releaseAfter)
{
$this->releaseAfter = $this->secondsUntil($releaseAfter);

return $this;
}







public function block($timeout)
{
$this->timeout = $timeout;

return $this;
}







public function sleep($sleep)
{
$this->sleep = $sleep;

return $this;
}










public function then(callable $callback, ?callable $failure = null)
{
try {
return (new ConcurrencyLimiter(
$this->connection, $this->name, $this->maxLocks, $this->releaseAfter
))->block($this->timeout, $callback, $this->sleep);
} catch (LimiterTimeoutException $e) {
if ($failure) {
return $failure($e);
}

throw $e;
}
}
}
