<?php










namespace Symfony\Component\HttpFoundation;


class_exists(ResponseHeaderBag::class);






class Response
{
public const HTTP_CONTINUE = 100;
public const HTTP_SWITCHING_PROTOCOLS = 101;
public const HTTP_PROCESSING = 102; 
public const HTTP_EARLY_HINTS = 103; 
public const HTTP_OK = 200;
public const HTTP_CREATED = 201;
public const HTTP_ACCEPTED = 202;
public const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
public const HTTP_NO_CONTENT = 204;
public const HTTP_RESET_CONTENT = 205;
public const HTTP_PARTIAL_CONTENT = 206;
public const HTTP_MULTI_STATUS = 207; 
public const HTTP_ALREADY_REPORTED = 208; 
public const HTTP_IM_USED = 226; 
public const HTTP_MULTIPLE_CHOICES = 300;
public const HTTP_MOVED_PERMANENTLY = 301;
public const HTTP_FOUND = 302;
public const HTTP_SEE_OTHER = 303;
public const HTTP_NOT_MODIFIED = 304;
public const HTTP_USE_PROXY = 305;
public const HTTP_RESERVED = 306;
public const HTTP_TEMPORARY_REDIRECT = 307;
public const HTTP_PERMANENTLY_REDIRECT = 308; 
public const HTTP_BAD_REQUEST = 400;
public const HTTP_UNAUTHORIZED = 401;
public const HTTP_PAYMENT_REQUIRED = 402;
public const HTTP_FORBIDDEN = 403;
public const HTTP_NOT_FOUND = 404;
public const HTTP_METHOD_NOT_ALLOWED = 405;
public const HTTP_NOT_ACCEPTABLE = 406;
public const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
public const HTTP_REQUEST_TIMEOUT = 408;
public const HTTP_CONFLICT = 409;
public const HTTP_GONE = 410;
public const HTTP_LENGTH_REQUIRED = 411;
public const HTTP_PRECONDITION_FAILED = 412;
public const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
public const HTTP_REQUEST_URI_TOO_LONG = 414;
public const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
public const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
public const HTTP_EXPECTATION_FAILED = 417;
public const HTTP_I_AM_A_TEAPOT = 418; 
public const HTTP_MISDIRECTED_REQUEST = 421; 
public const HTTP_UNPROCESSABLE_ENTITY = 422; 
public const HTTP_LOCKED = 423; 
public const HTTP_FAILED_DEPENDENCY = 424; 
public const HTTP_TOO_EARLY = 425; 
public const HTTP_UPGRADE_REQUIRED = 426; 
public const HTTP_PRECONDITION_REQUIRED = 428; 
public const HTTP_TOO_MANY_REQUESTS = 429; 
public const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431; 
public const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451; 
public const HTTP_INTERNAL_SERVER_ERROR = 500;
public const HTTP_NOT_IMPLEMENTED = 501;
public const HTTP_BAD_GATEWAY = 502;
public const HTTP_SERVICE_UNAVAILABLE = 503;
public const HTTP_GATEWAY_TIMEOUT = 504;
public const HTTP_VERSION_NOT_SUPPORTED = 505;
public const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506; 
public const HTTP_INSUFFICIENT_STORAGE = 507; 
public const HTTP_LOOP_DETECTED = 508; 
public const HTTP_NOT_EXTENDED = 510; 
public const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511; 




private const HTTP_RESPONSE_CACHE_CONTROL_DIRECTIVES = [
'must_revalidate' => false,
'no_cache' => false,
'no_store' => false,
'no_transform' => false,
'public' => false,
'private' => false,
'proxy_revalidate' => false,
'max_age' => true,
's_maxage' => true,
'stale_if_error' => true, 
'stale_while_revalidate' => true, 
'immutable' => false,
'last_modified' => true,
'etag' => true,
];

public ResponseHeaderBag $headers;

protected string $content;
protected string $version;
protected int $statusCode;
protected string $statusText;
protected ?string $charset = null;










public static array $statusTexts = [
100 => 'Continue',
101 => 'Switching Protocols',
102 => 'Processing', 
103 => 'Early Hints',
200 => 'OK',
201 => 'Created',
202 => 'Accepted',
203 => 'Non-Authoritative Information',
204 => 'No Content',
205 => 'Reset Content',
206 => 'Partial Content',
207 => 'Multi-Status', 
208 => 'Already Reported', 
226 => 'IM Used', 
300 => 'Multiple Choices',
301 => 'Moved Permanently',
302 => 'Found',
303 => 'See Other',
304 => 'Not Modified',
305 => 'Use Proxy',
307 => 'Temporary Redirect',
308 => 'Permanent Redirect', 
400 => 'Bad Request',
401 => 'Unauthorized',
402 => 'Payment Required',
403 => 'Forbidden',
404 => 'Not Found',
405 => 'Method Not Allowed',
406 => 'Not Acceptable',
407 => 'Proxy Authentication Required',
408 => 'Request Timeout',
409 => 'Conflict',
410 => 'Gone',
411 => 'Length Required',
412 => 'Precondition Failed',
413 => 'Content Too Large', 
414 => 'URI Too Long',
415 => 'Unsupported Media Type',
416 => 'Range Not Satisfiable',
417 => 'Expectation Failed',
418 => 'I\'m a teapot', 
421 => 'Misdirected Request', 
422 => 'Unprocessable Content', 
423 => 'Locked', 
424 => 'Failed Dependency', 
425 => 'Too Early', 
426 => 'Upgrade Required', 
428 => 'Precondition Required', 
429 => 'Too Many Requests', 
431 => 'Request Header Fields Too Large', 
451 => 'Unavailable For Legal Reasons', 
500 => 'Internal Server Error',
501 => 'Not Implemented',
502 => 'Bad Gateway',
503 => 'Service Unavailable',
504 => 'Gateway Timeout',
505 => 'HTTP Version Not Supported',
506 => 'Variant Also Negotiates', 
507 => 'Insufficient Storage', 
508 => 'Loop Detected', 
510 => 'Not Extended', 
511 => 'Network Authentication Required', 
];




private array $sentHeaders;






public function __construct(?string $content = '', int $status = 200, array $headers = [])
{
$this->headers = new ResponseHeaderBag($headers);
$this->setContent($content);
$this->setStatusCode($status);
$this->setProtocolVersion('1.0');
}










public function __toString(): string
{
return
sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText)."\r\n".
$this->headers."\r\n".
$this->getContent();
}




public function __clone()
{
$this->headers = clone $this->headers;
}










public function prepare(Request $request): static
{
$headers = $this->headers;

if ($this->isInformational() || $this->isEmpty()) {
$this->setContent(null);
$headers->remove('Content-Type');
$headers->remove('Content-Length');

ini_set('default_mimetype', '');
} else {

if (!$headers->has('Content-Type')) {
$format = $request->getRequestFormat(null);
if (null !== $format && $mimeType = $request->getMimeType($format)) {
$headers->set('Content-Type', $mimeType);
}
}


$charset = $this->charset ?: 'UTF-8';
if (!$headers->has('Content-Type')) {
$headers->set('Content-Type', 'text/html; charset='.$charset);
} elseif (0 === stripos($headers->get('Content-Type') ?? '', 'text/') && false === stripos($headers->get('Content-Type') ?? '', 'charset')) {

$headers->set('Content-Type', $headers->get('Content-Type').'; charset='.$charset);
}


if ($headers->has('Transfer-Encoding')) {
$headers->remove('Content-Length');
}

if ($request->isMethod('HEAD')) {

$length = $headers->get('Content-Length');
$this->setContent(null);
if ($length) {
$headers->set('Content-Length', $length);
}
}
}


if ('HTTP/1.0' != $request->server->get('SERVER_PROTOCOL')) {
$this->setProtocolVersion('1.1');
}


if ('1.0' == $this->getProtocolVersion() && str_contains($headers->get('Cache-Control', ''), 'no-cache')) {
$headers->set('pragma', 'no-cache');
$headers->set('expires', -1);
}

$this->ensureIEOverSSLCompatibility($request);

if ($request->isSecure()) {
foreach ($headers->getCookies() as $cookie) {
$cookie->setSecureDefault(true);
}
}

return $this;
}








public function sendHeaders(?int $statusCode = null): static
{

if (headers_sent()) {
return $this;
}

$informationalResponse = $statusCode >= 100 && $statusCode < 200;
if ($informationalResponse && !\function_exists('headers_send')) {

return $this;
}


foreach ($this->headers->allPreserveCaseWithoutCookies() as $name => $values) {

$previousValues = $this->sentHeaders[$name] ?? null;
if ($previousValues === $values) {

continue;
}

$replace = 0 === strcasecmp($name, 'Content-Type');

if (null !== $previousValues && array_diff($previousValues, $values)) {
header_remove($name);
$previousValues = null;
}

$newValues = null === $previousValues ? $values : array_diff($values, $previousValues);

foreach ($newValues as $value) {
header($name.': '.$value, $replace, $this->statusCode);
}

if ($informationalResponse) {
$this->sentHeaders[$name] = $values;
}
}


foreach ($this->headers->getCookies() as $cookie) {
header('Set-Cookie: '.$cookie, false, $this->statusCode);
}

if ($informationalResponse) {
headers_send($statusCode);

return $this;
}

$statusCode ??= $this->statusCode;


header(sprintf('HTTP/%s %s %s', $this->version, $statusCode, $this->statusText), true, $statusCode);

return $this;
}






public function sendContent(): static
{
echo $this->content;

return $this;
}








public function send(bool $flush = true): static
{
$this->sendHeaders();
$this->sendContent();

if (!$flush) {
return $this;
}

if (\function_exists('fastcgi_finish_request')) {
fastcgi_finish_request();
} elseif (\function_exists('litespeed_finish_request')) {
litespeed_finish_request();
} elseif (!\in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
static::closeOutputBuffers(0, true);
flush();
}

return $this;
}






public function setContent(?string $content): static
{
$this->content = $content ?? '';

return $this;
}




public function getContent(): string|false
{
return $this->content;
}








public function setProtocolVersion(string $version): static
{
$this->version = $version;

return $this;
}






public function getProtocolVersion(): string
{
return $this->version;
}













public function setStatusCode(int $code, ?string $text = null): static
{
$this->statusCode = $code;
if ($this->isInvalid()) {
throw new \InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $code));
}

if (null === $text) {
$this->statusText = self::$statusTexts[$code] ?? 'unknown status';

return $this;
}

$this->statusText = $text;

return $this;
}






public function getStatusCode(): int
{
return $this->statusCode;
}








public function setCharset(string $charset): static
{
$this->charset = $charset;

return $this;
}






public function getCharset(): ?string
{
return $this->charset;
}


















public function isCacheable(): bool
{
if (!\in_array($this->statusCode, [200, 203, 300, 301, 302, 404, 410])) {
return false;
}

if ($this->headers->hasCacheControlDirective('no-store') || $this->headers->getCacheControlDirective('private')) {
return false;
}

return $this->isValidateable() || $this->isFresh();
}










public function isFresh(): bool
{
return $this->getTtl() > 0;
}







public function isValidateable(): bool
{
return $this->headers->has('Last-Modified') || $this->headers->has('ETag');
}










public function setPrivate(): static
{
$this->headers->removeCacheControlDirective('public');
$this->headers->addCacheControlDirective('private');

return $this;
}










public function setPublic(): static
{
$this->headers->addCacheControlDirective('public');
$this->headers->removeCacheControlDirective('private');

return $this;
}








public function setImmutable(bool $immutable = true): static
{
if ($immutable) {
$this->headers->addCacheControlDirective('immutable');
} else {
$this->headers->removeCacheControlDirective('immutable');
}

return $this;
}






public function isImmutable(): bool
{
return $this->headers->hasCacheControlDirective('immutable');
}











public function mustRevalidate(): bool
{
return $this->headers->hasCacheControlDirective('must-revalidate') || $this->headers->hasCacheControlDirective('proxy-revalidate');
}








public function getDate(): ?\DateTimeImmutable
{
return $this->headers->getDate('Date');
}








public function setDate(\DateTimeInterface $date): static
{
$date = \DateTimeImmutable::createFromInterface($date);
$date = $date->setTimezone(new \DateTimeZone('UTC'));
$this->headers->set('Date', $date->format('D, d M Y H:i:s').' GMT');

return $this;
}






public function getAge(): int
{
if (null !== $age = $this->headers->get('Age')) {
return (int) $age;
}

return max(time() - (int) $this->getDate()->format('U'), 0);
}






public function expire(): static
{
if ($this->isFresh()) {
$this->headers->set('Age', $this->getMaxAge());
$this->headers->remove('Expires');
}

return $this;
}






public function getExpires(): ?\DateTimeImmutable
{
try {
return $this->headers->getDate('Expires');
} catch (\RuntimeException) {

return \DateTimeImmutable::createFromFormat('U', time() - 172800);
}
}










public function setExpires(?\DateTimeInterface $date): static
{
if (null === $date) {
$this->headers->remove('Expires');

return $this;
}

$date = \DateTimeImmutable::createFromInterface($date);
$date = $date->setTimezone(new \DateTimeZone('UTC'));
$this->headers->set('Expires', $date->format('D, d M Y H:i:s').' GMT');

return $this;
}










public function getMaxAge(): ?int
{
if ($this->headers->hasCacheControlDirective('s-maxage')) {
return (int) $this->headers->getCacheControlDirective('s-maxage');
}

if ($this->headers->hasCacheControlDirective('max-age')) {
return (int) $this->headers->getCacheControlDirective('max-age');
}

if (null !== $expires = $this->getExpires()) {
$maxAge = (int) $expires->format('U') - (int) $this->getDate()->format('U');

return max($maxAge, 0);
}

return null;
}










public function setMaxAge(int $value): static
{
$this->headers->addCacheControlDirective('max-age', $value);

return $this;
}










public function setStaleIfError(int $value): static
{
$this->headers->addCacheControlDirective('stale-if-error', $value);

return $this;
}










public function setStaleWhileRevalidate(int $value): static
{
$this->headers->addCacheControlDirective('stale-while-revalidate', $value);

return $this;
}










public function setSharedMaxAge(int $value): static
{
$this->setPublic();
$this->headers->addCacheControlDirective('s-maxage', $value);

return $this;
}











public function getTtl(): ?int
{
$maxAge = $this->getMaxAge();

return null !== $maxAge ? max($maxAge - $this->getAge(), 0) : null;
}










public function setTtl(int $seconds): static
{
$this->setSharedMaxAge($this->getAge() + $seconds);

return $this;
}










public function setClientTtl(int $seconds): static
{
$this->setMaxAge($this->getAge() + $seconds);

return $this;
}








public function getLastModified(): ?\DateTimeImmutable
{
return $this->headers->getDate('Last-Modified');
}










public function setLastModified(?\DateTimeInterface $date): static
{
if (null === $date) {
$this->headers->remove('Last-Modified');

return $this;
}

$date = \DateTimeImmutable::createFromInterface($date);
$date = $date->setTimezone(new \DateTimeZone('UTC'));
$this->headers->set('Last-Modified', $date->format('D, d M Y H:i:s').' GMT');

return $this;
}






public function getEtag(): ?string
{
return $this->headers->get('ETag');
}











public function setEtag(?string $etag, bool $weak = false): static
{
if (null === $etag) {
$this->headers->remove('Etag');
} else {
if (!str_starts_with($etag, '"')) {
$etag = '"'.$etag.'"';
}

$this->headers->set('ETag', (true === $weak ? 'W/' : '').$etag);
}

return $this;
}












public function setCache(array $options): static
{
if ($diff = array_diff(array_keys($options), array_keys(self::HTTP_RESPONSE_CACHE_CONTROL_DIRECTIVES))) {
throw new \InvalidArgumentException(sprintf('Response does not support the following options: "%s".', implode('", "', $diff)));
}

if (isset($options['etag'])) {
$this->setEtag($options['etag']);
}

if (isset($options['last_modified'])) {
$this->setLastModified($options['last_modified']);
}

if (isset($options['max_age'])) {
$this->setMaxAge($options['max_age']);
}

if (isset($options['s_maxage'])) {
$this->setSharedMaxAge($options['s_maxage']);
}

if (isset($options['stale_while_revalidate'])) {
$this->setStaleWhileRevalidate($options['stale_while_revalidate']);
}

if (isset($options['stale_if_error'])) {
$this->setStaleIfError($options['stale_if_error']);
}

foreach (self::HTTP_RESPONSE_CACHE_CONTROL_DIRECTIVES as $directive => $hasValue) {
if (!$hasValue && isset($options[$directive])) {
if ($options[$directive]) {
$this->headers->addCacheControlDirective(str_replace('_', '-', $directive));
} else {
$this->headers->removeCacheControlDirective(str_replace('_', '-', $directive));
}
}
}

if (isset($options['public'])) {
if ($options['public']) {
$this->setPublic();
} else {
$this->setPrivate();
}
}

if (isset($options['private'])) {
if ($options['private']) {
$this->setPrivate();
} else {
$this->setPublic();
}
}

return $this;
}













public function setNotModified(): static
{
$this->setStatusCode(304);
$this->setContent(null);


foreach (['Allow', 'Content-Encoding', 'Content-Language', 'Content-Length', 'Content-MD5', 'Content-Type', 'Last-Modified'] as $header) {
$this->headers->remove($header);
}

return $this;
}






public function hasVary(): bool
{
return null !== $this->headers->get('Vary');
}






public function getVary(): array
{
if (!$vary = $this->headers->all('Vary')) {
return [];
}

$ret = [];
foreach ($vary as $item) {
$ret[] = preg_split('/[\s,]+/', $item);
}

return array_merge([], ...$ret);
}










public function setVary(string|array $headers, bool $replace = true): static
{
$this->headers->set('Vary', $headers, $replace);

return $this;
}










public function isNotModified(Request $request): bool
{
if (!$request->isMethodCacheable()) {
return false;
}

$notModified = false;
$lastModified = $this->headers->get('Last-Modified');
$modifiedSince = $request->headers->get('If-Modified-Since');

if (($ifNoneMatchEtags = $request->getETags()) && (null !== $etag = $this->getEtag())) {
if (0 == strncmp($etag, 'W/', 2)) {
$etag = substr($etag, 2);
}


foreach ($ifNoneMatchEtags as $ifNoneMatchEtag) {
if (0 == strncmp($ifNoneMatchEtag, 'W/', 2)) {
$ifNoneMatchEtag = substr($ifNoneMatchEtag, 2);
}

if ($ifNoneMatchEtag === $etag || '*' === $ifNoneMatchEtag) {
$notModified = true;
break;
}
}
}

elseif ($modifiedSince && $lastModified) {
$notModified = strtotime($modifiedSince) >= strtotime($lastModified);
}

if ($notModified) {
$this->setNotModified();
}

return $notModified;
}








public function isInvalid(): bool
{
return $this->statusCode < 100 || $this->statusCode >= 600;
}






public function isInformational(): bool
{
return $this->statusCode >= 100 && $this->statusCode < 200;
}






public function isSuccessful(): bool
{
return $this->statusCode >= 200 && $this->statusCode < 300;
}






public function isRedirection(): bool
{
return $this->statusCode >= 300 && $this->statusCode < 400;
}






public function isClientError(): bool
{
return $this->statusCode >= 400 && $this->statusCode < 500;
}






public function isServerError(): bool
{
return $this->statusCode >= 500 && $this->statusCode < 600;
}






public function isOk(): bool
{
return 200 === $this->statusCode;
}






public function isForbidden(): bool
{
return 403 === $this->statusCode;
}






public function isNotFound(): bool
{
return 404 === $this->statusCode;
}






public function isRedirect(?string $location = null): bool
{
return \in_array($this->statusCode, [201, 301, 302, 303, 307, 308]) && (null === $location ?: $location == $this->headers->get('Location'));
}






public function isEmpty(): bool
{
return \in_array($this->statusCode, [204, 304]);
}








public static function closeOutputBuffers(int $targetLevel, bool $flush): void
{
$status = ob_get_status(true);
$level = \count($status);
$flags = \PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? \PHP_OUTPUT_HANDLER_FLUSHABLE : \PHP_OUTPUT_HANDLER_CLEANABLE);

while ($level-- > $targetLevel && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || ($s['flags'] & $flags) === $flags : $s['del'])) {
if ($flush) {
ob_end_flush();
} else {
ob_end_clean();
}
}
}






public function setContentSafe(bool $safe = true): void
{
if ($safe) {
$this->headers->set('Preference-Applied', 'safe');
} elseif ('safe' === $this->headers->get('Preference-Applied')) {
$this->headers->remove('Preference-Applied');
}

$this->setVary('Prefer', false);
}








protected function ensureIEOverSSLCompatibility(Request $request): void
{
if (false !== stripos($this->headers->get('Content-Disposition') ?? '', 'attachment') && 1 == preg_match('/MSIE (.*?);/i', $request->server->get('HTTP_USER_AGENT') ?? '', $match) && true === $request->isSecure()) {
if ((int) preg_replace('/(MSIE )(.*?);/', '$2', $match[0]) < 9) {
$this->headers->remove('Cache-Control');
}
}
}
}
