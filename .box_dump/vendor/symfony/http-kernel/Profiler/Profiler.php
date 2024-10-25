<?php










namespace Symfony\Component\HttpKernel\Profiler;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\ConflictingHeadersException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;
use Symfony\Contracts\Service\ResetInterface;






class Profiler implements ResetInterface
{



private array $collectors = [];

private bool $initiallyEnabled = true;

public function __construct(
private ProfilerStorageInterface $storage,
private ?LoggerInterface $logger = null,
private bool $enabled = true,
) {
$this->initiallyEnabled = $enabled;
}




public function disable(): void
{
$this->enabled = false;
}




public function enable(): void
{
$this->enabled = true;
}

public function isEnabled(): bool
{
return $this->enabled;
}




public function loadProfileFromResponse(Response $response): ?Profile
{
if (!$token = $response->headers->get('X-Debug-Token')) {
return null;
}

return $this->loadProfile($token);
}




public function loadProfile(string $token): ?Profile
{
return $this->storage->read($token);
}




public function saveProfile(Profile $profile): bool
{

foreach ($profile->getCollectors() as $collector) {
if ($collector instanceof LateDataCollectorInterface) {
$collector->lateCollect();
}
}

if (!($ret = $this->storage->write($profile)) && null !== $this->logger) {
$this->logger->warning('Unable to store the profiler information.', ['configured_storage' => $this->storage::class]);
}

return $ret;
}




public function purge(): void
{
$this->storage->purge();
}











public function find(?string $ip, ?string $url, ?int $limit, ?string $method, ?string $start, ?string $end, ?string $statusCode = null, ?\Closure $filter = null): array
{
return $this->storage->find($ip, $url, $limit, $method, $this->getTimestamp($start), $this->getTimestamp($end), $statusCode, $filter);
}




public function collect(Request $request, Response $response, ?\Throwable $exception = null): ?Profile
{
if (false === $this->enabled) {
return null;
}

$profile = new Profile(substr(hash('xxh128', uniqid(mt_rand(), true)), 0, 6));
$profile->setTime(time());
$profile->setUrl($request->getUri());
$profile->setMethod($request->getMethod());
$profile->setStatusCode($response->getStatusCode());
try {
$profile->setIp($request->getClientIp());
} catch (ConflictingHeadersException) {
$profile->setIp('Unknown');
}

if ($request->attributes->has('_virtual_type')) {
$profile->setVirtualType($request->attributes->get('_virtual_type'));
}

if ($prevToken = $response->headers->get('X-Debug-Token')) {
$response->headers->set('X-Previous-Debug-Token', $prevToken);
}

$response->headers->set('X-Debug-Token', $profile->getToken());

foreach ($this->collectors as $collector) {
$collector->collect($request, $response, $exception);


$profile->addCollector(clone $collector);
}

return $profile;
}

public function reset(): void
{
foreach ($this->collectors as $collector) {
$collector->reset();
}
$this->enabled = $this->initiallyEnabled;
}




public function all(): array
{
return $this->collectors;
}






public function set(array $collectors = []): void
{
$this->collectors = [];
foreach ($collectors as $collector) {
$this->add($collector);
}
}




public function add(DataCollectorInterface $collector): void
{
$this->collectors[$collector->getName()] = $collector;
}






public function has(string $name): bool
{
return isset($this->collectors[$name]);
}








public function get(string $name): DataCollectorInterface
{
if (!isset($this->collectors[$name])) {
throw new \InvalidArgumentException(sprintf('Collector "%s" does not exist.', $name));
}

return $this->collectors[$name];
}

private function getTimestamp(?string $value): ?int
{
if (null === $value || '' === $value) {
return null;
}

try {
$value = new \DateTimeImmutable(is_numeric($value) ? '@'.$value : $value);
} catch (\Exception) {
return null;
}

return $value->getTimestamp();
}
}
