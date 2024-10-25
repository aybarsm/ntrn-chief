<?php










namespace Symfony\Component\HttpFoundation;






class Cookie
{
public const SAMESITE_NONE = 'none';
public const SAMESITE_LAX = 'lax';
public const SAMESITE_STRICT = 'strict';

protected string $name;
protected ?string $value;
protected ?string $domain;
protected int $expire;
protected string $path;
protected ?bool $secure;
protected bool $httpOnly;

private bool $raw;
private ?string $sameSite = null;
private bool $partitioned = false;
private bool $secureDefault = false;

private const RESERVED_CHARS_LIST = "=,; \t\r\n\v\f";
private const RESERVED_CHARS_FROM = ['=', ',', ';', ' ', "\t", "\r", "\n", "\v", "\f"];
private const RESERVED_CHARS_TO = ['%3D', '%2C', '%3B', '%20', '%09', '%0D', '%0A', '%0B', '%0C'];




public static function fromString(string $cookie, bool $decode = false): static
{
$data = [
'expires' => 0,
'path' => '/',
'domain' => null,
'secure' => false,
'httponly' => false,
'raw' => !$decode,
'samesite' => null,
'partitioned' => false,
];

$parts = HeaderUtils::split($cookie, ';=');
$part = array_shift($parts);

$name = $decode ? urldecode($part[0]) : $part[0];
$value = isset($part[1]) ? ($decode ? urldecode($part[1]) : $part[1]) : null;

$data = HeaderUtils::combine($parts) + $data;
$data['expires'] = self::expiresTimestamp($data['expires']);

if (isset($data['max-age']) && ($data['max-age'] > 0 || $data['expires'] > time())) {
$data['expires'] = time() + (int) $data['max-age'];
}

return new static($name, $value, $data['expires'], $data['path'], $data['domain'], $data['secure'], $data['httponly'], $data['raw'], $data['samesite'], $data['partitioned']);
}






public static function create(string $name, ?string $value = null, int|string|\DateTimeInterface $expire = 0, ?string $path = '/', ?string $domain = null, ?bool $secure = null, bool $httpOnly = true, bool $raw = false, ?string $sameSite = self::SAMESITE_LAX, bool $partitioned = false): self
{
return new self($name, $value, $expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite, $partitioned);
}














public function __construct(string $name, ?string $value = null, int|string|\DateTimeInterface $expire = 0, ?string $path = '/', ?string $domain = null, ?bool $secure = null, bool $httpOnly = true, bool $raw = false, ?string $sameSite = self::SAMESITE_LAX, bool $partitioned = false)
{

if ($raw && false !== strpbrk($name, self::RESERVED_CHARS_LIST)) {
throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
}

if (!$name) {
throw new \InvalidArgumentException('The cookie name cannot be empty.');
}

$this->name = $name;
$this->value = $value;
$this->domain = $domain;
$this->expire = self::expiresTimestamp($expire);
$this->path = $path ?: '/';
$this->secure = $secure;
$this->httpOnly = $httpOnly;
$this->raw = $raw;
$this->sameSite = $this->withSameSite($sameSite)->sameSite;
$this->partitioned = $partitioned;
}




public function withValue(?string $value): static
{
$cookie = clone $this;
$cookie->value = $value;

return $cookie;
}




public function withDomain(?string $domain): static
{
$cookie = clone $this;
$cookie->domain = $domain;

return $cookie;
}




public function withExpires(int|string|\DateTimeInterface $expire = 0): static
{
$cookie = clone $this;
$cookie->expire = self::expiresTimestamp($expire);

return $cookie;
}




private static function expiresTimestamp(int|string|\DateTimeInterface $expire = 0): int
{

if ($expire instanceof \DateTimeInterface) {
$expire = $expire->format('U');
} elseif (!is_numeric($expire)) {
$expire = strtotime($expire);

if (false === $expire) {
throw new \InvalidArgumentException('The cookie expiration time is not valid.');
}
}

return 0 < $expire ? (int) $expire : 0;
}




public function withPath(string $path): static
{
$cookie = clone $this;
$cookie->path = '' === $path ? '/' : $path;

return $cookie;
}




public function withSecure(bool $secure = true): static
{
$cookie = clone $this;
$cookie->secure = $secure;

return $cookie;
}




public function withHttpOnly(bool $httpOnly = true): static
{
$cookie = clone $this;
$cookie->httpOnly = $httpOnly;

return $cookie;
}




public function withRaw(bool $raw = true): static
{
if ($raw && false !== strpbrk($this->name, self::RESERVED_CHARS_LIST)) {
throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $this->name));
}

$cookie = clone $this;
$cookie->raw = $raw;

return $cookie;
}






public function withSameSite(?string $sameSite): static
{
if ('' === $sameSite) {
$sameSite = null;
} elseif (null !== $sameSite) {
$sameSite = strtolower($sameSite);
}

if (!\in_array($sameSite, [self::SAMESITE_LAX, self::SAMESITE_STRICT, self::SAMESITE_NONE, null], true)) {
throw new \InvalidArgumentException('The "sameSite" parameter value is not valid.');
}

$cookie = clone $this;
$cookie->sameSite = $sameSite;

return $cookie;
}




public function withPartitioned(bool $partitioned = true): static
{
$cookie = clone $this;
$cookie->partitioned = $partitioned;

return $cookie;
}




public function __toString(): string
{
if ($this->isRaw()) {
$str = $this->getName();
} else {
$str = str_replace(self::RESERVED_CHARS_FROM, self::RESERVED_CHARS_TO, $this->getName());
}

$str .= '=';

if ('' === (string) $this->getValue()) {
$str .= 'deleted; expires='.gmdate('D, d M Y H:i:s T', time() - 31536001).'; Max-Age=0';
} else {
$str .= $this->isRaw() ? $this->getValue() : rawurlencode($this->getValue());

if (0 !== $this->getExpiresTime()) {
$str .= '; expires='.gmdate('D, d M Y H:i:s T', $this->getExpiresTime()).'; Max-Age='.$this->getMaxAge();
}
}

if ($this->getPath()) {
$str .= '; path='.$this->getPath();
}

if ($this->getDomain()) {
$str .= '; domain='.$this->getDomain();
}

if ($this->isSecure()) {
$str .= '; secure';
}

if ($this->isHttpOnly()) {
$str .= '; httponly';
}

if (null !== $this->getSameSite()) {
$str .= '; samesite='.$this->getSameSite();
}

if ($this->isPartitioned()) {
$str .= '; partitioned';
}

return $str;
}




public function getName(): string
{
return $this->name;
}




public function getValue(): ?string
{
return $this->value;
}




public function getDomain(): ?string
{
return $this->domain;
}




public function getExpiresTime(): int
{
return $this->expire;
}




public function getMaxAge(): int
{
$maxAge = $this->expire - time();

return max(0, $maxAge);
}




public function getPath(): string
{
return $this->path;
}




public function isSecure(): bool
{
return $this->secure ?? $this->secureDefault;
}




public function isHttpOnly(): bool
{
return $this->httpOnly;
}




public function isCleared(): bool
{
return 0 !== $this->expire && $this->expire < time();
}




public function isRaw(): bool
{
return $this->raw;
}




public function isPartitioned(): bool
{
return $this->partitioned;
}




public function getSameSite(): ?string
{
return $this->sameSite;
}




public function setSecureDefault(bool $default): void
{
$this->secureDefault = $default;
}
}
