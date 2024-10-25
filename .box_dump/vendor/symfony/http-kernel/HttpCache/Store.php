<?php













namespace Symfony\Component\HttpKernel\HttpCache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;






class Store implements StoreInterface
{

private \SplObjectStorage $keyCache;

private array $locks = [];











public function __construct(
protected string $root,
private array $options = [],
) {
if (!is_dir($this->root) && !@mkdir($this->root, 0777, true) && !is_dir($this->root)) {
throw new \RuntimeException(sprintf('Unable to create the store directory (%s).', $this->root));
}
$this->keyCache = new \SplObjectStorage();
$this->options['private_headers'] ??= ['Set-Cookie'];
}




public function cleanup(): void
{

foreach ($this->locks as $lock) {
flock($lock, \LOCK_UN);
fclose($lock);
}

$this->locks = [];
}






public function lock(Request $request): bool|string
{
$key = $this->getCacheKey($request);

if (!isset($this->locks[$key])) {
$path = $this->getPath($key);
if (!is_dir(\dirname($path)) && false === @mkdir(\dirname($path), 0777, true) && !is_dir(\dirname($path))) {
return $path;
}
$h = fopen($path, 'c');
if (!flock($h, \LOCK_EX | \LOCK_NB)) {
fclose($h);

return $path;
}

$this->locks[$key] = $h;
}

return true;
}






public function unlock(Request $request): bool
{
$key = $this->getCacheKey($request);

if (isset($this->locks[$key])) {
flock($this->locks[$key], \LOCK_UN);
fclose($this->locks[$key]);
unset($this->locks[$key]);

return true;
}

return false;
}

public function isLocked(Request $request): bool
{
$key = $this->getCacheKey($request);

if (isset($this->locks[$key])) {
return true; 
}

if (!is_file($path = $this->getPath($key))) {
return false;
}

$h = fopen($path, 'r');
flock($h, \LOCK_EX | \LOCK_NB, $wouldBlock);
flock($h, \LOCK_UN); 
fclose($h);

return (bool) $wouldBlock;
}




public function lookup(Request $request): ?Response
{
$key = $this->getCacheKey($request);

if (!$entries = $this->getMetadata($key)) {
return null;
}


$match = null;
foreach ($entries as $entry) {
if ($this->requestsMatch(isset($entry[1]['vary'][0]) ? implode(', ', $entry[1]['vary']) : '', $request->headers->all(), $entry[0])) {
$match = $entry;

break;
}
}

if (null === $match) {
return null;
}

$headers = $match[1];
if (file_exists($path = $this->getPath($headers['x-content-digest'][0]))) {
return $this->restoreResponse($headers, $path);
}




return null;
}









public function write(Request $request, Response $response): string
{
$key = $this->getCacheKey($request);
$storedEnv = $this->persistRequest($request);

if ($response->headers->has('X-Body-File')) {

if (!$response->headers->has('X-Content-Digest')) {
throw new \RuntimeException('A restored response must have the X-Content-Digest header.');
}

$digest = $response->headers->get('X-Content-Digest');
if ($this->getPath($digest) !== $response->headers->get('X-Body-File')) {
throw new \RuntimeException('X-Body-File and X-Content-Digest do not match.');
}

} else {
$digest = $this->generateContentDigest($response);
$response->headers->set('X-Content-Digest', $digest);

if (!$this->save($digest, $response->getContent(), false)) {
throw new \RuntimeException('Unable to store the entity.');
}

if (!$response->headers->has('Transfer-Encoding')) {
$response->headers->set('Content-Length', \strlen($response->getContent()));
}
}


$entries = [];
$vary = $response->headers->get('vary');
foreach ($this->getMetadata($key) as $entry) {
if (!isset($entry[1]['vary'][0])) {
$entry[1]['vary'] = [''];
}

if ($entry[1]['vary'][0] != $vary || !$this->requestsMatch($vary ?? '', $entry[0], $storedEnv)) {
$entries[] = $entry;
}
}

$headers = $this->persistResponse($response);
unset($headers['age']);

foreach ($this->options['private_headers'] as $h) {
unset($headers[strtolower($h)]);
}

array_unshift($entries, [$storedEnv, $headers]);

if (!$this->save($key, serialize($entries))) {
throw new \RuntimeException('Unable to store the metadata.');
}

return $key;
}




protected function generateContentDigest(Response $response): string
{
return 'en'.hash('xxh128', $response->getContent());
}






public function invalidate(Request $request): void
{
$modified = false;
$key = $this->getCacheKey($request);

$entries = [];
foreach ($this->getMetadata($key) as $entry) {
$response = $this->restoreResponse($entry[1]);
if ($response->isFresh()) {
$response->expire();
$modified = true;
$entries[] = [$entry[0], $this->persistResponse($response)];
} else {
$entries[] = $entry;
}
}

if ($modified && !$this->save($key, serialize($entries))) {
throw new \RuntimeException('Unable to store the metadata.');
}
}









private function requestsMatch(?string $vary, array $env1, array $env2): bool
{
if (!$vary) {
return true;
}

foreach (preg_split('/[\s,]+/', $vary) as $header) {
$key = str_replace('_', '-', strtolower($header));
$v1 = $env1[$key] ?? null;
$v2 = $env2[$key] ?? null;
if ($v1 !== $v2) {
return false;
}
}

return true;
}






private function getMetadata(string $key): array
{
if (!$entries = $this->load($key)) {
return [];
}

return unserialize($entries) ?: [];
}








public function purge(string $url): bool
{
$http = preg_replace('#^https:#', 'http:', $url);
$https = preg_replace('#^http:#', 'https:', $url);

$purgedHttp = $this->doPurge($http);
$purgedHttps = $this->doPurge($https);

return $purgedHttp || $purgedHttps;
}




private function doPurge(string $url): bool
{
$key = $this->getCacheKey(Request::create($url));
if (isset($this->locks[$key])) {
flock($this->locks[$key], \LOCK_UN);
fclose($this->locks[$key]);
unset($this->locks[$key]);
}

if (is_file($path = $this->getPath($key))) {
unlink($path);

return true;
}

return false;
}




private function load(string $key): ?string
{
$path = $this->getPath($key);

return is_file($path) && false !== ($contents = @file_get_contents($path)) ? $contents : null;
}




private function save(string $key, string $data, bool $overwrite = true): bool
{
$path = $this->getPath($key);

if (!$overwrite && file_exists($path)) {
return true;
}

if (isset($this->locks[$key])) {
$fp = $this->locks[$key];
@ftruncate($fp, 0);
@fseek($fp, 0);
$len = @fwrite($fp, $data);
if (\strlen($data) !== $len) {
@ftruncate($fp, 0);

return false;
}
} else {
if (!is_dir(\dirname($path)) && false === @mkdir(\dirname($path), 0777, true) && !is_dir(\dirname($path))) {
return false;
}

$tmpFile = tempnam(\dirname($path), basename($path));
if (false === $fp = @fopen($tmpFile, 'w')) {
@unlink($tmpFile);

return false;
}
@fwrite($fp, $data);
@fclose($fp);

if ($data != file_get_contents($tmpFile)) {
@unlink($tmpFile);

return false;
}

if (false === @rename($tmpFile, $path)) {
@unlink($tmpFile);

return false;
}
}

@chmod($path, 0666 & ~umask());

return true;
}

public function getPath(string $key): string
{
return $this->root.\DIRECTORY_SEPARATOR.substr($key, 0, 2).\DIRECTORY_SEPARATOR.substr($key, 2, 2).\DIRECTORY_SEPARATOR.substr($key, 4, 2).\DIRECTORY_SEPARATOR.substr($key, 6);
}











protected function generateCacheKey(Request $request): string
{
return 'md'.hash('sha256', $request->getUri());
}




private function getCacheKey(Request $request): string
{
if (isset($this->keyCache[$request])) {
return $this->keyCache[$request];
}

return $this->keyCache[$request] = $this->generateCacheKey($request);
}




private function persistRequest(Request $request): array
{
return $request->headers->all();
}




private function persistResponse(Response $response): array
{
$headers = $response->headers->all();
$headers['X-Status'] = [$response->getStatusCode()];

return $headers;
}




private function restoreResponse(array $headers, ?string $path = null): ?Response
{
$status = $headers['X-Status'][0];
unset($headers['X-Status']);
$content = null;

if (null !== $path) {
$headers['X-Body-File'] = [$path];
unset($headers['x-body-file']);

if ($headers['X-Body-Eval'] ?? $headers['x-body-eval'] ?? false) {
$content = file_get_contents($path);
\assert(HttpCache::BODY_EVAL_BOUNDARY_LENGTH === 24);
if (48 > \strlen($content) || substr($content, -24) !== substr($content, 0, 24)) {
return null;
}
}
}

return new Response($content, $status, $headers);
}
}
