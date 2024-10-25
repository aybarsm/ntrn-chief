<?php

namespace Illuminate\Redis\Connections;

use Closure;
use Illuminate\Contracts\Redis\Connection as ConnectionContract;
use Predis\Command\Argument\ArrayableArgument;

/**
@mixin
*/
class PredisConnection extends Connection implements ConnectionContract
{





protected $client;







public function __construct($client)
{
$this->client = $client;
}









public function createSubscription($channels, Closure $callback, $method = 'subscribe')
{
$loop = $this->pubSubLoop();

$loop->{$method}(...array_values((array) $channels));

foreach ($loop as $message) {
if ($message->kind === 'message' || $message->kind === 'pmessage') {
$callback($message->payload, $message->channel);
}
}

unset($loop);
}







protected function parseParametersForEvent(array $parameters)
{
return collect($parameters)
->transform(function ($parameter) {
return $parameter instanceof ArrayableArgument
? $parameter->toArray()
: $parameter;
})->all();
}
}
