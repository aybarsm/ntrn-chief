<?php










namespace Symfony\Component\HttpFoundation;






class ResponseHeaderBag extends HeaderBag
{
public const COOKIES_FLAT = 'flat';
public const COOKIES_ARRAY = 'array';

public const DISPOSITION_ATTACHMENT = 'attachment';
public const DISPOSITION_INLINE = 'inline';

protected array $computedCacheControl = [];
protected array $cookies = [];
protected array $headerNames = [];

public function __construct(array $headers = [])
{
parent::__construct($headers);

if (!isset($this->headers['cache-control'])) {
$this->set('Cache-Control', '');
}


if (!isset($this->headers['date'])) {
$this->initDate();
}
}




public function allPreserveCase(): array
{
$headers = [];
foreach ($this->all() as $name => $value) {
$headers[$this->headerNames[$name] ?? $name] = $value;
}

return $headers;
}

public function allPreserveCaseWithoutCookies(): array
{
$headers = $this->allPreserveCase();
if (isset($this->headerNames['set-cookie'])) {
unset($headers[$this->headerNames['set-cookie']]);
}

return $headers;
}

public function replace(array $headers = []): void
{
$this->headerNames = [];

parent::replace($headers);

if (!isset($this->headers['cache-control'])) {
$this->set('Cache-Control', '');
}

if (!isset($this->headers['date'])) {
$this->initDate();
}
}

public function all(?string $key = null): array
{
$headers = parent::all();

if (null !== $key) {
$key = strtr($key, self::UPPER, self::LOWER);

return 'set-cookie' !== $key ? $headers[$key] ?? [] : array_map('strval', $this->getCookies());
}

foreach ($this->getCookies() as $cookie) {
$headers['set-cookie'][] = (string) $cookie;
}

return $headers;
}

public function set(string $key, string|array|null $values, bool $replace = true): void
{
$uniqueKey = strtr($key, self::UPPER, self::LOWER);

if ('set-cookie' === $uniqueKey) {
if ($replace) {
$this->cookies = [];
}
foreach ((array) $values as $cookie) {
$this->setCookie(Cookie::fromString($cookie));
}
$this->headerNames[$uniqueKey] = $key;

return;
}

$this->headerNames[$uniqueKey] = $key;

parent::set($key, $values, $replace);


if (\in_array($uniqueKey, ['cache-control', 'etag', 'last-modified', 'expires'], true) && '' !== $computed = $this->computeCacheControlValue()) {
$this->headers['cache-control'] = [$computed];
$this->headerNames['cache-control'] = 'Cache-Control';
$this->computedCacheControl = $this->parseCacheControl($computed);
}
}

public function remove(string $key): void
{
$uniqueKey = strtr($key, self::UPPER, self::LOWER);
unset($this->headerNames[$uniqueKey]);

if ('set-cookie' === $uniqueKey) {
$this->cookies = [];

return;
}

parent::remove($key);

if ('cache-control' === $uniqueKey) {
$this->computedCacheControl = [];
}

if ('date' === $uniqueKey) {
$this->initDate();
}
}

public function hasCacheControlDirective(string $key): bool
{
return \array_key_exists($key, $this->computedCacheControl);
}

public function getCacheControlDirective(string $key): bool|string|null
{
return $this->computedCacheControl[$key] ?? null;
}

public function setCookie(Cookie $cookie): void
{
$this->cookies[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
$this->headerNames['set-cookie'] = 'Set-Cookie';
}




public function removeCookie(string $name, ?string $path = '/', ?string $domain = null): void
{
$path ??= '/';

unset($this->cookies[$domain][$path][$name]);

if (empty($this->cookies[$domain][$path])) {
unset($this->cookies[$domain][$path]);

if (empty($this->cookies[$domain])) {
unset($this->cookies[$domain]);
}
}

if (!$this->cookies) {
unset($this->headerNames['set-cookie']);
}
}








public function getCookies(string $format = self::COOKIES_FLAT): array
{
if (!\in_array($format, [self::COOKIES_FLAT, self::COOKIES_ARRAY])) {
throw new \InvalidArgumentException(sprintf('Format "%s" invalid (%s).', $format, implode(', ', [self::COOKIES_FLAT, self::COOKIES_ARRAY])));
}

if (self::COOKIES_ARRAY === $format) {
return $this->cookies;
}

$flattenedCookies = [];
foreach ($this->cookies as $path) {
foreach ($path as $cookies) {
foreach ($cookies as $cookie) {
$flattenedCookies[] = $cookie;
}
}
}

return $flattenedCookies;
}






public function clearCookie(string $name, ?string $path = '/', ?string $domain = null, bool $secure = false, bool $httpOnly = true, ?string $sameSite = null ): void
{
$partitioned = 6 < \func_num_args() ? \func_get_arg(6) : false;

$this->setCookie(new Cookie($name, null, 1, $path, $domain, $secure, $httpOnly, false, $sameSite, $partitioned));
}




public function makeDisposition(string $disposition, string $filename, string $filenameFallback = ''): string
{
return HeaderUtils::makeDisposition($disposition, $filename, $filenameFallback);
}







protected function computeCacheControlValue(): string
{
if (!$this->cacheControl) {
if ($this->has('Last-Modified') || $this->has('Expires')) {
return 'private, must-revalidate'; 
}


return 'no-cache, private';
}

$header = $this->getCacheControlHeader();
if (isset($this->cacheControl['public']) || isset($this->cacheControl['private'])) {
return $header;
}


if (!isset($this->cacheControl['s-maxage'])) {
return $header.', private';
}

return $header;
}

private function initDate(): void
{
$this->set('Date', gmdate('D, d M Y H:i:s').' GMT');
}
}
