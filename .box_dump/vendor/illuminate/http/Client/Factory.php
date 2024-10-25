<?php

namespace Illuminate\Http\Client;

use Closure;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response as Psr7Response;
use GuzzleHttp\TransferStats;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use PHPUnit\Framework\Assert as PHPUnit;

/**
@mixin
*/
class Factory
{
use Macroable {
__call as macroCall;
}






protected $dispatcher;






protected $globalMiddleware = [];






protected $globalOptions = [];






protected $stubCallbacks;






protected $recording = false;






protected $recorded = [];






protected $responseSequences = [];






protected $preventStrayRequests = false;







public function __construct(?Dispatcher $dispatcher = null)
{
$this->dispatcher = $dispatcher;

$this->stubCallbacks = collect();
}







public function globalMiddleware($middleware)
{
$this->globalMiddleware[] = $middleware;

return $this;
}







public function globalRequestMiddleware($middleware)
{
$this->globalMiddleware[] = Middleware::mapRequest($middleware);

return $this;
}







public function globalResponseMiddleware($middleware)
{
$this->globalMiddleware[] = Middleware::mapResponse($middleware);

return $this;
}







public function globalOptions($options)
{
$this->globalOptions = $options;

return $this;
}









public static function response($body = null, $status = 200, $headers = [])
{
if (is_array($body)) {
$body = json_encode($body);

$headers['Content-Type'] = 'application/json';
}

$response = new Psr7Response($status, $headers, $body);

return Create::promiseFor($response);
}







public function sequence(array $responses = [])
{
return $this->responseSequences[] = new ResponseSequence($responses);
}







public function fake($callback = null)
{
$this->record();

$this->recorded = [];

if (is_null($callback)) {
$callback = function () {
return static::response();
};
}

if (is_array($callback)) {
foreach ($callback as $url => $callable) {
$this->stubUrl($url, $callable);
}

return $this;
}

$this->stubCallbacks = $this->stubCallbacks->merge(collect([
function ($request, $options) use ($callback) {
$response = $callback instanceof Closure
? $callback($request, $options)
: $callback;

if ($response instanceof PromiseInterface) {
$options['on_stats'](new TransferStats(
$request->toPsrRequest(),
$response->wait(),
));
}

return $response;
},
]));

return $this;
}







public function fakeSequence($url = '*')
{
return tap($this->sequence(), function ($sequence) use ($url) {
$this->fake([$url => $sequence]);
});
}








public function stubUrl($url, $callback)
{
return $this->fake(function ($request, $options) use ($url, $callback) {
if (! Str::is(Str::start($url, '*'), $request->url())) {
return;
}

return $callback instanceof Closure || $callback instanceof ResponseSequence
? $callback($request, $options)
: $callback;
});
}







public function preventStrayRequests($prevent = true)
{
$this->preventStrayRequests = $prevent;

return $this;
}






public function preventingStrayRequests()
{
return $this->preventStrayRequests;
}






public function allowStrayRequests()
{
return $this->preventStrayRequests(false);
}






protected function record()
{
$this->recording = true;

return $this;
}








public function recordRequestResponsePair($request, $response)
{
if ($this->recording) {
$this->recorded[] = [$request, $response];
}
}







public function assertSent($callback)
{
PHPUnit::assertTrue(
$this->recorded($callback)->count() > 0,
'An expected request was not recorded.'
);
}







public function assertSentInOrder($callbacks)
{
$this->assertSentCount(count($callbacks));

foreach ($callbacks as $index => $url) {
$callback = is_callable($url) ? $url : function ($request) use ($url) {
return $request->url() == $url;
};

PHPUnit::assertTrue($callback(
$this->recorded[$index][0],
$this->recorded[$index][1]
), 'An expected request (#'.($index + 1).') was not recorded.');
}
}







public function assertNotSent($callback)
{
PHPUnit::assertFalse(
$this->recorded($callback)->count() > 0,
'Unexpected request was recorded.'
);
}






public function assertNothingSent()
{
PHPUnit::assertEmpty(
$this->recorded,
'Requests were recorded.'
);
}







public function assertSentCount($count)
{
PHPUnit::assertCount($count, $this->recorded);
}






public function assertSequencesAreEmpty()
{
foreach ($this->responseSequences as $responseSequence) {
PHPUnit::assertTrue(
$responseSequence->isEmpty(),
'Not all response sequences are empty.'
);
}
}







public function recorded($callback = null)
{
if (empty($this->recorded)) {
return collect();
}

$callback = $callback ?: function () {
return true;
};

return collect($this->recorded)->filter(function ($pair) use ($callback) {
return $callback($pair[0], $pair[1]);
});
}






public function createPendingRequest()
{
return tap($this->newPendingRequest(), function ($request) {
$request->stub($this->stubCallbacks)->preventStrayRequests($this->preventStrayRequests);
});
}






protected function newPendingRequest()
{
return (new PendingRequest($this, $this->globalMiddleware))->withOptions(value($this->globalOptions));
}






public function getDispatcher()
{
return $this->dispatcher;
}






public function getGlobalMiddleware()
{
return $this->globalMiddleware;
}








public function __call($method, $parameters)
{
if (static::hasMacro($method)) {
return $this->macroCall($method, $parameters);
}

return $this->createPendingRequest()->{$method}(...$parameters);
}
}
