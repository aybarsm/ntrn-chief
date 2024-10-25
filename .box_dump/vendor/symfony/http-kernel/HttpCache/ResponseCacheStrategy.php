<?php










namespace Symfony\Component\HttpKernel\HttpCache;

use Symfony\Component\HttpFoundation\Response;










class ResponseCacheStrategy implements ResponseCacheStrategyInterface
{



private const OVERRIDE_DIRECTIVES = ['private', 'no-cache', 'no-store', 'no-transform', 'must-revalidate', 'proxy-revalidate'];




private const INHERIT_DIRECTIVES = ['public', 'immutable'];

private int $embeddedResponses = 0;
private bool $isNotCacheableResponseEmbedded = false;
private int $age = 0;
private \DateTimeInterface|false|null $lastModified = null;
private array $flagDirectives = [
'no-cache' => null,
'no-store' => null,
'no-transform' => null,
'must-revalidate' => null,
'proxy-revalidate' => null,
'public' => null,
'private' => null,
'immutable' => null,
];
private array $ageDirectives = [
'max-age' => null,
's-maxage' => null,
'expires' => null,
];

public function add(Response $response): void
{
++$this->embeddedResponses;

foreach (self::OVERRIDE_DIRECTIVES as $directive) {
if ($response->headers->hasCacheControlDirective($directive)) {
$this->flagDirectives[$directive] = true;
}
}

foreach (self::INHERIT_DIRECTIVES as $directive) {
if (false !== $this->flagDirectives[$directive]) {
$this->flagDirectives[$directive] = $response->headers->hasCacheControlDirective($directive);
}
}

$age = $response->getAge();
$this->age = max($this->age, $age);

if ($this->willMakeFinalResponseUncacheable($response)) {
$this->isNotCacheableResponseEmbedded = true;

return;
}

$isHeuristicallyCacheable = $response->headers->hasCacheControlDirective('public');
$maxAge = $response->headers->hasCacheControlDirective('max-age') ? (int) $response->headers->getCacheControlDirective('max-age') : null;
$this->storeRelativeAgeDirective('max-age', $maxAge, $age, $isHeuristicallyCacheable);
$sharedMaxAge = $response->headers->hasCacheControlDirective('s-maxage') ? (int) $response->headers->getCacheControlDirective('s-maxage') : $maxAge;
$this->storeRelativeAgeDirective('s-maxage', $sharedMaxAge, $age, $isHeuristicallyCacheable);

$expires = $response->getExpires();
$expires = null !== $expires ? (int) $expires->format('U') - (int) $response->getDate()->format('U') : null;
$this->storeRelativeAgeDirective('expires', $expires >= 0 ? $expires : null, 0, $isHeuristicallyCacheable);

if (false !== $this->lastModified) {
$lastModified = $response->getLastModified();
$this->lastModified = $lastModified ? max($this->lastModified, $lastModified) : false;
}
}

public function update(Response $response): void
{

if (0 === $this->embeddedResponses) {
return;
}


$response->setEtag(null);

$this->add($response);

$response->headers->set('Age', $this->age);

if ($this->isNotCacheableResponseEmbedded) {
$response->setLastModified(null);

if ($this->flagDirectives['no-store']) {
$response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
} else {
$response->headers->set('Cache-Control', 'no-cache, must-revalidate');
}

return;
}

$response->setLastModified($this->lastModified ?: null);

$flags = array_filter($this->flagDirectives);

if (isset($flags['must-revalidate'])) {
$flags['no-cache'] = true;
}

$response->headers->set('Cache-Control', implode(', ', array_keys($flags)));

$maxAge = null;

if (is_numeric($this->ageDirectives['max-age'])) {
$maxAge = $this->ageDirectives['max-age'] + $this->age;
$response->headers->addCacheControlDirective('max-age', $maxAge);
}

if (is_numeric($this->ageDirectives['s-maxage'])) {
$sMaxage = $this->ageDirectives['s-maxage'] + $this->age;

if ($maxAge !== $sMaxage) {
$response->headers->addCacheControlDirective('s-maxage', $sMaxage);
}
}

if (is_numeric($this->ageDirectives['expires'])) {
$date = clone $response->getDate();
$date = $date->modify('+'.($this->ageDirectives['expires'] + $this->age).' seconds');
$response->setExpires($date);
}
}






private function willMakeFinalResponseUncacheable(Response $response): bool
{


if ($response->headers->hasCacheControlDirective('no-cache')
|| $response->headers->hasCacheControlDirective('no-store')
) {
return true;
}



if (null === $response->getEtag() && \in_array($response->getStatusCode(), [200, 203, 300, 301, 410])) {
return false;
}




$cacheControl = ['max-age', 's-maxage', 'must-revalidate', 'proxy-revalidate', 'public', 'private'];
foreach ($cacheControl as $key) {
if ($response->headers->hasCacheControlDirective($key)) {
return false;
}
}

if ($response->headers->has('Expires')) {
return false;
}

return true;
}



















private function storeRelativeAgeDirective(string $directive, ?int $value, int $age, bool $isHeuristicallyCacheable): void
{
if (null === $value) {
if ($isHeuristicallyCacheable) {






return;
}
$this->ageDirectives[$directive] = false;
}

if (false !== $this->ageDirectives[$directive]) {
$value -= $age;
$this->ageDirectives[$directive] = null !== $this->ageDirectives[$directive] ? min($this->ageDirectives[$directive], $value) : $value;
}
}
}
