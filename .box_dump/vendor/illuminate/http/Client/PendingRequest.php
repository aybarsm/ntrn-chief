<?php

namespace Illuminate\Http\Client;

use Closure;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\UriTemplate\UriTemplate;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use JsonSerializable;
use OutOfBoundsException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use RuntimeException;
use Symfony\Component\VarDumper\VarDumper;

class PendingRequest
{
use Conditionable, Macroable;






protected $factory;






protected $client;






protected $handler;






protected $baseUrl = '';






protected $urlParameters = [];






protected $bodyFormat;






protected $pendingBody;






protected $pendingFiles = [];






protected $cookies;






protected $transferStats;






protected $options = [];






protected $throwCallback;






protected $throwIfCallback;






protected $tries = 1;






protected $retryDelay = 100;






protected $retryThrow = true;






protected $retryWhenCallback = null;






protected $beforeSendingCallbacks;






protected $stubCallbacks;






protected $preventStrayRequests = false;






protected $middleware;






protected $async = false;






protected $promise;






protected $request;






protected $mergeableOptions = [
'cookies',
'form_params',
'headers',
'json',
'multipart',
'query',
];








public function __construct(?Factory $factory = null, $middleware = [])
{
$this->factory = $factory;
$this->middleware = new Collection($middleware);

$this->asJson();

$this->options = [
'connect_timeout' => 10,
'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
'http_errors' => false,
'timeout' => 30,
];

$this->beforeSendingCallbacks = collect([function (Request $request, array $options, PendingRequest $pendingRequest) {
$pendingRequest->request = $request;
$pendingRequest->cookies = $options['cookies'];

$pendingRequest->dispatchRequestSendingEvent();
}]);
}







public function baseUrl(string $url)
{
$this->baseUrl = $url;

return $this;
}








public function withBody($content, $contentType = 'application/json')
{
$this->bodyFormat('body');

$this->pendingBody = $content;

$this->contentType($contentType);

return $this;
}






public function asJson()
{
return $this->bodyFormat('json')->contentType('application/json');
}






public function asForm()
{
return $this->bodyFormat('form_params')->contentType('application/x-www-form-urlencoded');
}










public function attach($name, $contents = '', $filename = null, array $headers = [])
{
if (is_array($name)) {
foreach ($name as $file) {
$this->attach(...$file);
}

return $this;
}

$this->asMultipart();

$this->pendingFiles[] = array_filter([
'name' => $name,
'contents' => $contents,
'headers' => $headers,
'filename' => $filename,
]);

return $this;
}






public function asMultipart()
{
return $this->bodyFormat('multipart');
}







public function bodyFormat(string $format)
{
return tap($this, function () use ($format) {
$this->bodyFormat = $format;
});
}







public function withQueryParameters(array $parameters)
{
return tap($this, function () use ($parameters) {
$this->options = array_merge_recursive($this->options, [
'query' => $parameters,
]);
});
}







public function contentType(string $contentType)
{
$this->options['headers']['Content-Type'] = $contentType;

return $this;
}






public function acceptJson()
{
return $this->accept('application/json');
}







public function accept($contentType)
{
return $this->withHeaders(['Accept' => $contentType]);
}







public function withHeaders(array $headers)
{
return tap($this, function () use ($headers) {
$this->options = array_merge_recursive($this->options, [
'headers' => $headers,
]);
});
}








public function withHeader($name, $value)
{
return $this->withHeaders([$name => $value]);
}







public function replaceHeaders(array $headers)
{
$this->options['headers'] = array_merge($this->options['headers'] ?? [], $headers);

return $this;
}








public function withBasicAuth(string $username, string $password)
{
return tap($this, function () use ($username, $password) {
$this->options['auth'] = [$username, $password];
});
}








public function withDigestAuth($username, $password)
{
return tap($this, function () use ($username, $password) {
$this->options['auth'] = [$username, $password, 'digest'];
});
}








public function withToken($token, $type = 'Bearer')
{
return tap($this, function () use ($token, $type) {
$this->options['headers']['Authorization'] = trim($type.' '.$token);
});
}







public function withUserAgent($userAgent)
{
return tap($this, function () use ($userAgent) {
$this->options['headers']['User-Agent'] = trim($userAgent);
});
}







public function withUrlParameters(array $parameters = [])
{
return tap($this, function () use ($parameters) {
$this->urlParameters = $parameters;
});
}








public function withCookies(array $cookies, string $domain)
{
return tap($this, function () use ($cookies, $domain) {
$this->options = array_merge_recursive($this->options, [
'cookies' => CookieJar::fromArray($cookies, $domain),
]);
});
}







public function maxRedirects(int $max)
{
return tap($this, function () use ($max) {
$this->options['allow_redirects']['max'] = $max;
});
}






public function withoutRedirecting()
{
return tap($this, function () {
$this->options['allow_redirects'] = false;
});
}






public function withoutVerifying()
{
return tap($this, function () {
$this->options['verify'] = false;
});
}







public function sink($to)
{
return tap($this, function () use ($to) {
$this->options['sink'] = $to;
});
}







public function timeout(int $seconds)
{
return tap($this, function () use ($seconds) {
$this->options['timeout'] = $seconds;
});
}







public function connectTimeout(int $seconds)
{
return tap($this, function () use ($seconds) {
$this->options['connect_timeout'] = $seconds;
});
}










public function retry(array|int $times, Closure|int $sleepMilliseconds = 0, ?callable $when = null, bool $throw = true)
{
$this->tries = $times;
$this->retryDelay = $sleepMilliseconds;
$this->retryThrow = $throw;
$this->retryWhenCallback = $when;

return $this;
}







public function withOptions(array $options)
{
return tap($this, function () use ($options) {
$this->options = array_replace_recursive(
array_merge_recursive($this->options, Arr::only($options, $this->mergeableOptions)),
$options
);
});
}







public function withMiddleware(callable $middleware)
{
$this->middleware->push($middleware);

return $this;
}







public function withRequestMiddleware(callable $middleware)
{
$this->middleware->push(Middleware::mapRequest($middleware));

return $this;
}







public function withResponseMiddleware(callable $middleware)
{
$this->middleware->push(Middleware::mapResponse($middleware));

return $this;
}







public function beforeSending($callback)
{
return tap($this, function () use ($callback) {
$this->beforeSendingCallbacks[] = $callback;
});
}







public function throw(?callable $callback = null)
{
$this->throwCallback = $callback ?: fn () => null;

return $this;
}







public function throwIf($condition)
{
if (is_callable($condition)) {
$this->throwIfCallback = $condition;
}

return $condition ? $this->throw(func_get_args()[1] ?? null) : $this;
}







public function throwUnless($condition)
{
return $this->throwIf(! $condition);
}






public function dump()
{
$values = func_get_args();

return $this->beforeSending(function (Request $request, array $options) use ($values) {
foreach (array_merge($values, [$request, $options]) as $value) {
VarDumper::dump($value);
}
});
}






public function dd()
{
$values = func_get_args();

return $this->beforeSending(function (Request $request, array $options) use ($values) {
foreach (array_merge($values, [$request, $options]) as $value) {
VarDumper::dump($value);
}

exit(1);
});
}










public function get(string $url, $query = null)
{
return $this->send('GET', $url, func_num_args() === 1 ? [] : [
'query' => $query,
]);
}










public function head(string $url, $query = null)
{
return $this->send('HEAD', $url, func_num_args() === 1 ? [] : [
'query' => $query,
]);
}










public function post(string $url, $data = [])
{
return $this->send('POST', $url, [
$this->bodyFormat => $data,
]);
}










public function patch(string $url, $data = [])
{
return $this->send('PATCH', $url, [
$this->bodyFormat => $data,
]);
}










public function put(string $url, $data = [])
{
return $this->send('PUT', $url, [
$this->bodyFormat => $data,
]);
}










public function delete(string $url, $data = [])
{
return $this->send('DELETE', $url, empty($data) ? [] : [
$this->bodyFormat => $data,
]);
}







public function pool(callable $callback)
{
$results = [];

$requests = tap(new Pool($this->factory), $callback)->getRequests();

foreach ($requests as $key => $item) {
$results[$key] = $item instanceof static ? $item->getPromise()->wait() : $item->wait();
}

return $results;
}












public function send(string $method, string $url, array $options = [])
{
if (! Str::startsWith($url, ['http://', 'https://'])) {
$url = ltrim(rtrim($this->baseUrl, '/').'/'.ltrim($url, '/'), '/');
}

$url = $this->expandUrlParameters($url);

$options = $this->parseHttpOptions($options);

[$this->pendingBody, $this->pendingFiles] = [null, []];

if ($this->async) {
return $this->makePromise($method, $url, $options);
}

$shouldRetry = null;

return retry($this->tries ?? 1, function ($attempt) use ($method, $url, $options, &$shouldRetry) {
try {
return tap($this->newResponse($this->sendRequest($method, $url, $options)), function ($response) use ($attempt, &$shouldRetry) {
$this->populateResponse($response);

$this->dispatchResponseReceivedEvent($response);

if (! $response->successful()) {
try {
$shouldRetry = $this->retryWhenCallback ? call_user_func($this->retryWhenCallback, $response->toException(), $this) : true;
} catch (Exception $exception) {
$shouldRetry = false;

throw $exception;
}

if ($this->throwCallback &&
($this->throwIfCallback === null ||
call_user_func($this->throwIfCallback, $response))) {
$response->throw($this->throwCallback);
}

$potentialTries = is_array($this->tries)
? count($this->tries) + 1
: $this->tries;

if ($attempt < $potentialTries && $shouldRetry) {
$response->throw();
}

if ($potentialTries > 1 && $this->retryThrow) {
$response->throw();
}
}
});
} catch (ConnectException $e) {
$exception = new ConnectionException($e->getMessage(), 0, $e);

$this->dispatchConnectionFailedEvent(new Request($e->getRequest()), $exception);

throw $exception;
}
}, $this->retryDelay ?? 100, function ($exception) use (&$shouldRetry) {
$result = $shouldRetry ?? ($this->retryWhenCallback ? call_user_func($this->retryWhenCallback, $exception, $this) : true);

$shouldRetry = null;

return $result;
});
}







protected function expandUrlParameters(string $url)
{
return UriTemplate::expand($url, $this->urlParameters);
}







protected function parseHttpOptions(array $options)
{
if (isset($options[$this->bodyFormat])) {
if ($this->bodyFormat === 'multipart') {
$options[$this->bodyFormat] = $this->parseMultipartBodyFormat($options[$this->bodyFormat]);
} elseif ($this->bodyFormat === 'body') {
$options[$this->bodyFormat] = $this->pendingBody;
}

if (is_array($options[$this->bodyFormat])) {
$options[$this->bodyFormat] = array_merge(
$options[$this->bodyFormat], $this->pendingFiles
);
}
} else {
$options[$this->bodyFormat] = $this->pendingBody;
}

return collect($options)->map(function ($value, $key) {
if ($key === 'json' && $value instanceof JsonSerializable) {
return $value;
}

return $value instanceof Arrayable ? $value->toArray() : $value;
})->all();
}







protected function parseMultipartBodyFormat(array $data)
{
return collect($data)->map(function ($value, $key) {
return is_array($value) ? $value : ['name' => $key, 'contents' => $value];
})->values()->all();
}










protected function makePromise(string $method, string $url, array $options = [], int $attempt = 1)
{
return $this->promise = $this->sendRequest($method, $url, $options)
->then(function (MessageInterface $message) {
return tap($this->newResponse($message), function ($response) {
$this->populateResponse($response);
$this->dispatchResponseReceivedEvent($response);
});
})
->otherwise(function (OutOfBoundsException|TransferException $e) {
if ($e instanceof ConnectException || ($e instanceof RequestException && ! $e->hasResponse())) {
$exception = new ConnectionException($e->getMessage(), 0, $e);

$this->dispatchConnectionFailedEvent(new Request($e->getRequest()), $exception);

return $exception;
}

return $e instanceof RequestException && $e->hasResponse() ? $this->populateResponse($this->newResponse($e->getResponse())) : $e;
})
->then(function (Response|ConnectionException|TransferException $response) use ($method, $url, $options, $attempt) {
return $this->handlePromiseResponse($response, $method, $url, $options, $attempt);
});
}











protected function handlePromiseResponse(Response|ConnectionException|TransferException $response, $method, $url, $options, $attempt)
{
if ($response instanceof Response && $response->successful()) {
return $response;
}

if ($response instanceof RequestException) {
$response = $this->populateResponse($this->newResponse($response->getResponse()));
}

try {
$shouldRetry = $this->retryWhenCallback ? call_user_func(
$this->retryWhenCallback,
$response instanceof Response ? $response->toException() : $response,
$this
) : true;
} catch (Exception $exception) {
return $exception;
}

if ($attempt < $this->tries && $shouldRetry) {
$options['delay'] = value(
$this->retryDelay,
$attempt,
$response instanceof Response ? $response->toException() : $response
);

return $this->makePromise($method, $url, $options, $attempt + 1);
}

if ($response instanceof Response &&
$this->throwCallback &&
($this->throwIfCallback === null || call_user_func($this->throwIfCallback, $response))) {
try {
$response->throw($this->throwCallback);
} catch (Exception $exception) {
return $exception;
}
}

if ($this->tries > 1 && $this->retryThrow) {
return $response instanceof Response ? $response->toException() : $response;
}

return $response;
}











protected function sendRequest(string $method, string $url, array $options = [])
{
$clientMethod = $this->async ? 'requestAsync' : 'request';

$laravelData = $this->parseRequestData($method, $url, $options);

$onStats = function ($transferStats) {
if (($callback = ($this->options['on_stats'] ?? false)) instanceof Closure) {
$transferStats = $callback($transferStats) ?: $transferStats;
}

$this->transferStats = $transferStats;
};

$mergedOptions = $this->normalizeRequestOptions($this->mergeOptions([
'laravel_data' => $laravelData,
'on_stats' => $onStats,
], $options));

return $this->buildClient()->$clientMethod($method, $url, $mergedOptions);
}









protected function parseRequestData($method, $url, array $options)
{
if ($this->bodyFormat === 'body') {
return [];
}

$laravelData = $options[$this->bodyFormat] ?? $options['query'] ?? [];

$urlString = Str::of($url);

if (empty($laravelData) && $method === 'GET' && $urlString->contains('?')) {
$laravelData = (string) $urlString->after('?');
}

if (is_string($laravelData)) {
parse_str($laravelData, $parsedData);

$laravelData = is_array($parsedData) ? $parsedData : [];
}

if ($laravelData instanceof JsonSerializable) {
$laravelData = $laravelData->jsonSerialize();
}

return is_array($laravelData) ? $laravelData : [];
}







protected function normalizeRequestOptions(array $options)
{
foreach ($options as $key => $value) {
$options[$key] = match (true) {
is_array($value) => $this->normalizeRequestOptions($value),
$value instanceof Stringable => $value->toString(),
default => $value,
};
}

return $options;
}







protected function populateResponse(Response $response)
{
$response->cookies = $this->cookies;

$response->transferStats = $this->transferStats;

return $response;
}






public function buildClient()
{
return $this->client ?? $this->createClient($this->buildHandlerStack());
}






protected function requestsReusableClient()
{
return ! is_null($this->client) || $this->async;
}






protected function getReusableClient()
{
return $this->client ??= $this->createClient($this->buildHandlerStack());
}







public function createClient($handlerStack)
{
return new Client([
'handler' => $handlerStack,
'cookies' => true,
]);
}






public function buildHandlerStack()
{
return $this->pushHandlers(HandlerStack::create($this->handler));
}







public function pushHandlers($handlerStack)
{
return tap($handlerStack, function ($stack) {
$this->middleware->each(function ($middleware) use ($stack) {
$stack->push($middleware);
});

$stack->push($this->buildBeforeSendingHandler());
$stack->push($this->buildRecorderHandler());
$stack->push($this->buildStubHandler());
});
}






public function buildBeforeSendingHandler()
{
return function ($handler) {
return function ($request, $options) use ($handler) {
return $handler($this->runBeforeSendingCallbacks($request, $options), $options);
};
};
}






public function buildRecorderHandler()
{
return function ($handler) {
return function ($request, $options) use ($handler) {
$promise = $handler($request, $options);

return $promise->then(function ($response) use ($request, $options) {
$this->factory?->recordRequestResponsePair(
(new Request($request))->withData($options['laravel_data']),
$this->newResponse($response)
);

return $response;
});
};
};
}






public function buildStubHandler()
{
return function ($handler) {
return function ($request, $options) use ($handler) {
$response = ($this->stubCallbacks ?? collect())
->map
->__invoke((new Request($request))->withData($options['laravel_data']), $options)
->filter()
->first();

if (is_null($response)) {
if ($this->preventStrayRequests) {
throw new RuntimeException('Attempted request to ['.(string) $request->getUri().'] without a matching fake.');
}

return $handler($request, $options);
}

$response = is_array($response) ? Factory::response($response) : $response;

$sink = $options['sink'] ?? null;

if ($sink) {
$response->then($this->sinkStubHandler($sink));
}

return $response;
};
};
}







protected function sinkStubHandler($sink)
{
return function ($response) use ($sink) {
$body = $response->getBody()->getContents();

if (is_string($sink)) {
file_put_contents($sink, $body);

return;
}

fwrite($sink, $body);
rewind($sink);
};
}








public function runBeforeSendingCallbacks($request, array $options)
{
return tap($request, function (&$request) use ($options) {
$this->beforeSendingCallbacks->each(function ($callback) use (&$request, $options) {
$callbackResult = call_user_func(
$callback, (new Request($request))->withData($options['laravel_data']), $options, $this
);

if ($callbackResult instanceof RequestInterface) {
$request = $callbackResult;
} elseif ($callbackResult instanceof Request) {
$request = $callbackResult->toPsrRequest();
}
});
});
}







public function mergeOptions(...$options)
{
return array_replace_recursive(
array_merge_recursive($this->options, Arr::only($options, $this->mergeableOptions)),
...$options
);
}







protected function newResponse($response)
{
return new Response($response);
}







public function stub($callback)
{
$this->stubCallbacks = collect($callback);

return $this;
}







public function preventStrayRequests($prevent = true)
{
$this->preventStrayRequests = $prevent;

return $this;
}







public function async(bool $async = true)
{
$this->async = $async;

return $this;
}






public function getPromise()
{
return $this->promise;
}






protected function dispatchRequestSendingEvent()
{
if ($dispatcher = $this->factory?->getDispatcher()) {
$dispatcher->dispatch(new RequestSending($this->request));
}
}







protected function dispatchResponseReceivedEvent(Response $response)
{
if (! ($dispatcher = $this->factory?->getDispatcher()) || ! $this->request) {
return;
}

$dispatcher->dispatch(new ResponseReceived($this->request, $response));
}








protected function dispatchConnectionFailedEvent(Request $request, ConnectionException $exception)
{
if ($dispatcher = $this->factory?->getDispatcher()) {
$dispatcher->dispatch(new ConnectionFailed($request, $exception));
}
}







public function setClient(Client $client)
{
$this->client = $client;

return $this;
}







public function setHandler($handler)
{
$this->handler = $handler;

return $this;
}






public function getOptions()
{
return $this->options;
}
}
