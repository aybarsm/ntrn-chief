<?php
















namespace Symfony\Component\HttpKernel\HttpCache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;






class HttpCache implements HttpKernelInterface, TerminableInterface
{
public const BODY_EVAL_BOUNDARY_LENGTH = 24;

private Request $request;
private ?ResponseCacheStrategyInterface $surrogateCacheStrategy = null;
private array $options = [];
private array $traces = [];














































public function __construct(
private HttpKernelInterface $kernel,
private StoreInterface $store,
private ?SurrogateInterface $surrogate = null,
array $options = [],
) {


register_shutdown_function($this->store->cleanup(...));

$this->options = array_merge([
'debug' => false,
'default_ttl' => 0,
'private_headers' => ['Authorization', 'Cookie'],
'skip_response_headers' => ['Set-Cookie'],
'allow_reload' => false,
'allow_revalidate' => false,
'stale_while_revalidate' => 2,
'stale_if_error' => 60,
'trace_level' => 'none',
'trace_header' => 'X-Symfony-Cache',
], $options);

if (!isset($options['trace_level'])) {
$this->options['trace_level'] = $this->options['debug'] ? 'full' : 'none';
}
}




public function getStore(): StoreInterface
{
return $this->store;
}




public function getTraces(): array
{
return $this->traces;
}

private function addTraces(Response $response): void
{
$traceString = null;

if ('full' === $this->options['trace_level']) {
$traceString = $this->getLog();
}

if ('short' === $this->options['trace_level'] && $masterId = array_key_first($this->traces)) {
$traceString = implode('/', $this->traces[$masterId]);
}

if (null !== $traceString) {
$response->headers->add([$this->options['trace_header'] => $traceString]);
}
}




public function getLog(): string
{
$log = [];
foreach ($this->traces as $request => $traces) {
$log[] = sprintf('%s: %s', $request, implode(', ', $traces));
}

return implode('; ', $log);
}




public function getRequest(): Request
{
return $this->request;
}




public function getKernel(): HttpKernelInterface
{
return $this->kernel;
}






public function getSurrogate(): SurrogateInterface
{
return $this->surrogate;
}

public function handle(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST, bool $catch = true): Response
{

if (HttpKernelInterface::MAIN_REQUEST === $type) {
$this->traces = [];




$this->request = clone $request;
if (null !== $this->surrogate) {
$this->surrogateCacheStrategy = $this->surrogate->createCacheStrategy();
}
}

$this->traces[$this->getTraceKey($request)] = [];

if (!$request->isMethodSafe()) {
$response = $this->invalidate($request, $catch);
} elseif ($request->headers->has('expect') || !$request->isMethodCacheable()) {
$response = $this->pass($request, $catch);
} elseif ($this->options['allow_reload'] && $request->isNoCache()) {




$this->record($request, 'reload');
$response = $this->fetch($request, $catch);
} else {
$response = $this->lookup($request, $catch);
}

$this->restoreResponseBody($request, $response);

if (HttpKernelInterface::MAIN_REQUEST === $type) {
$this->addTraces($response);
}

if (null !== $this->surrogate) {
if (HttpKernelInterface::MAIN_REQUEST === $type) {
$this->surrogateCacheStrategy->update($response);
} else {
$this->surrogateCacheStrategy->add($response);
}
}

$response->prepare($request);

if (HttpKernelInterface::MAIN_REQUEST === $type) {
$response->isNotModified($request);
}

return $response;
}

public function terminate(Request $request, Response $response): void
{



if (\in_array('fresh', $this->traces[$this->getTraceKey($request)] ?? [], true)) {
return;
}

if ($this->getKernel() instanceof TerminableInterface) {
$this->getKernel()->terminate($request, $response);
}
}






protected function pass(Request $request, bool $catch = false): Response
{
$this->record($request, 'pass');

return $this->forward($request, $catch);
}










protected function invalidate(Request $request, bool $catch = false): Response
{
$response = $this->pass($request, $catch);


if ($response->isSuccessful() || $response->isRedirect()) {
try {
$this->store->invalidate($request);


foreach (['Location', 'Content-Location'] as $header) {
if ($uri = $response->headers->get($header)) {
$subRequest = Request::create($uri, 'get', [], [], [], $request->server->all());

$this->store->invalidate($subRequest);
}
}

$this->record($request, 'invalidate');
} catch (\Exception $e) {
$this->record($request, 'invalidate-failed');

if ($this->options['debug']) {
throw $e;
}
}
}

return $response;
}














protected function lookup(Request $request, bool $catch = false): Response
{
try {
$entry = $this->store->lookup($request);
} catch (\Exception $e) {
$this->record($request, 'lookup-failed');

if ($this->options['debug']) {
throw $e;
}

return $this->pass($request, $catch);
}

if (null === $entry) {
$this->record($request, 'miss');

return $this->fetch($request, $catch);
}

if (!$this->isFreshEnough($request, $entry)) {
$this->record($request, 'stale');

return $this->validate($request, $entry, $catch);
}

if ($entry->headers->hasCacheControlDirective('no-cache')) {
return $this->validate($request, $entry, $catch);
}

$this->record($request, 'fresh');

$entry->headers->set('Age', $entry->getAge());

return $entry;
}









protected function validate(Request $request, Response $entry, bool $catch = false): Response
{
$subRequest = clone $request;


if ('HEAD' === $request->getMethod()) {
$subRequest->setMethod('GET');
}


if ($entry->headers->has('Last-Modified')) {
$subRequest->headers->set('If-Modified-Since', $entry->headers->get('Last-Modified'));
}




$cachedEtags = $entry->getEtag() ? [$entry->getEtag()] : [];
$requestEtags = $request->getETags();
if ($etags = array_unique(array_merge($cachedEtags, $requestEtags))) {
$subRequest->headers->set('If-None-Match', implode(', ', $etags));
}

$response = $this->forward($subRequest, $catch, $entry);

if (304 == $response->getStatusCode()) {
$this->record($request, 'valid');


$etag = $response->getEtag();
if ($etag && \in_array($etag, $requestEtags, true) && !\in_array($etag, $cachedEtags, true)) {
return $response;
}

$entry = clone $entry;
$entry->headers->remove('Date');

foreach (['Date', 'Expires', 'Cache-Control', 'ETag', 'Last-Modified'] as $name) {
if ($response->headers->has($name)) {
$entry->headers->set($name, $response->headers->get($name));
}
}

$response = $entry;
} else {
$this->record($request, 'invalid');
}

if ($response->isCacheable()) {
$this->store($request, $response);
}

return $response;
}







protected function fetch(Request $request, bool $catch = false): Response
{
$subRequest = clone $request;


if ('HEAD' === $request->getMethod()) {
$subRequest->setMethod('GET');
}


$subRequest->headers->remove('If-Modified-Since');
$subRequest->headers->remove('If-None-Match');

$response = $this->forward($subRequest, $catch);

if ($response->isCacheable()) {
$this->store($request, $response);
}

return $response;
}










protected function forward(Request $request, bool $catch = false, ?Response $entry = null): Response
{
$this->surrogate?->addSurrogateCapability($request);


$response = SubRequestHandler::handle($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST, $catch);

















if (null !== $entry
&& \in_array($response->getStatusCode(), [500, 502, 503, 504])
&& !$entry->headers->hasCacheControlDirective('no-cache')
&& !$entry->mustRevalidate()
) {
if (null === $age = $entry->headers->getCacheControlDirective('stale-if-error')) {
$age = $this->options['stale_if_error'];
}






if ($entry->getAge() <= $entry->getMaxAge() + $age) {
$this->record($request, 'stale-if-error');

return $entry;
}
}








if (!$response->headers->has('Date')) {
$response->setDate(\DateTimeImmutable::createFromFormat('U', time()));
}

$this->processResponseBody($request, $response);

if ($this->isPrivateRequest($request) && !$response->headers->hasCacheControlDirective('public')) {
$response->setPrivate();
} elseif ($this->options['default_ttl'] > 0 && null === $response->getTtl() && !$response->headers->getCacheControlDirective('must-revalidate')) {
$response->setTtl($this->options['default_ttl']);
}

return $response;
}




protected function isFreshEnough(Request $request, Response $entry): bool
{
if (!$entry->isFresh()) {
return $this->lock($request, $entry);
}

if ($this->options['allow_revalidate'] && null !== $maxAge = $request->headers->getCacheControlDirective('max-age')) {
return $maxAge > 0 && $maxAge >= $entry->getAge();
}

return true;
}






protected function lock(Request $request, Response $entry): bool
{

$lock = $this->store->lock($request);

if (true === $lock) {

return false;
}




if ($this->mayServeStaleWhileRevalidate($entry)) {
$this->record($request, 'stale-while-revalidate');

return true;
}


if ($this->waitForLock($request)) {

$new = $this->lookup($request);
$entry->headers = $new->headers;
$entry->setContent($new->getContent());
$entry->setStatusCode($new->getStatusCode());
$entry->setProtocolVersion($new->getProtocolVersion());
foreach ($new->headers->getCookies() as $cookie) {
$entry->headers->setCookie($cookie);
}
} else {

$entry->setStatusCode(503);
$entry->setContent('503 Service Unavailable');
$entry->headers->set('Retry-After', 10);
}

return true;
}






protected function store(Request $request, Response $response): void
{
try {
$restoreHeaders = [];
foreach ($this->options['skip_response_headers'] as $header) {
if (!$response->headers->has($header)) {
continue;
}

$restoreHeaders[$header] = $response->headers->all($header);
$response->headers->remove($header);
}

$this->store->write($request, $response);
$this->record($request, 'store');

$response->headers->set('Age', $response->getAge());
} catch (\Exception $e) {
$this->record($request, 'store-failed');

if ($this->options['debug']) {
throw $e;
}
} finally {
foreach ($restoreHeaders as $header => $values) {
$response->headers->set($header, $values);
}
}


$this->store->unlock($request);
}




private function restoreResponseBody(Request $request, Response $response): void
{
if ($response->headers->has('X-Body-Eval')) {
\assert(self::BODY_EVAL_BOUNDARY_LENGTH === 24);

ob_start();

$content = $response->getContent();
$boundary = substr($content, 0, 24);
$j = strpos($content, $boundary, 24);
echo substr($content, 24, $j - 24);
$i = $j + 24;

while (false !== $j = strpos($content, $boundary, $i)) {
[$uri, $alt, $ignoreErrors, $part] = explode("\n", substr($content, $i, $j - $i), 4);
$i = $j + 24;

echo $this->surrogate->handle($this, $uri, $alt, $ignoreErrors);
echo $part;
}

$response->setContent(ob_get_clean());
$response->headers->remove('X-Body-Eval');
if (!$response->headers->has('Transfer-Encoding')) {
$response->headers->set('Content-Length', \strlen($response->getContent()));
}
} elseif ($response->headers->has('X-Body-File')) {


if (!$request->isMethod('HEAD')) {
$response->setContent(file_get_contents($response->headers->get('X-Body-File')));
}
} else {
return;
}

$response->headers->remove('X-Body-File');
}

protected function processResponseBody(Request $request, Response $response): void
{
if ($this->surrogate?->needsParsing($response)) {
$this->surrogate->process($request, $response);
}
}





private function isPrivateRequest(Request $request): bool
{
foreach ($this->options['private_headers'] as $key) {
$key = strtolower(str_replace('HTTP_', '', $key));

if ('cookie' === $key) {
if (\count($request->cookies->all())) {
return true;
}
} elseif ($request->headers->has($key)) {
return true;
}
}

return false;
}




private function record(Request $request, string $event): void
{
$this->traces[$this->getTraceKey($request)][] = $event;
}




private function getTraceKey(Request $request): string
{
$path = $request->getPathInfo();
if ($qs = $request->getQueryString()) {
$path .= '?'.$qs;
}

return $request->getMethod().' '.$path;
}





private function mayServeStaleWhileRevalidate(Response $entry): bool
{
$timeout = $entry->headers->getCacheControlDirective('stale-while-revalidate');
$timeout ??= $this->options['stale_while_revalidate'];

$age = $entry->getAge();
$maxAge = $entry->getMaxAge() ?? 0;
$ttl = $maxAge - $age;

return abs($ttl) < $timeout;
}




private function waitForLock(Request $request): bool
{
$wait = 0;
while ($this->store->isLocked($request) && $wait < 100) {
usleep(50000);
++$wait;
}

return $wait < 100;
}
}
