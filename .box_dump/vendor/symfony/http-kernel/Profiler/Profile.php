<?php










namespace Symfony\Component\HttpKernel\Profiler;

use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;






class Profile
{




private array $collectors = [];

private ?string $ip = null;
private ?string $method = null;
private ?string $url = null;
private ?int $time = null;
private ?int $statusCode = null;
private ?self $parent = null;
private ?string $virtualType = null;




private array $children = [];

public function __construct(
private string $token,
) {
}

public function setToken(string $token): void
{
$this->token = $token;
}




public function getToken(): string
{
return $this->token;
}




public function setParent(self $parent): void
{
$this->parent = $parent;
}




public function getParent(): ?self
{
return $this->parent;
}




public function getParentToken(): ?string
{
return $this->parent?->getToken();
}




public function getIp(): ?string
{
return $this->ip;
}

public function setIp(?string $ip): void
{
$this->ip = $ip;
}




public function getMethod(): ?string
{
return $this->method;
}

public function setMethod(string $method): void
{
$this->method = $method;
}




public function getUrl(): ?string
{
return $this->url;
}

public function setUrl(?string $url): void
{
$this->url = $url;
}

public function getTime(): int
{
return $this->time ?? 0;
}

public function setTime(int $time): void
{
$this->time = $time;
}

public function setStatusCode(int $statusCode): void
{
$this->statusCode = $statusCode;
}

public function getStatusCode(): ?int
{
return $this->statusCode;
}




public function setVirtualType(?string $virtualType): void
{
$this->virtualType = $virtualType;
}




public function getVirtualType(): ?string
{
return $this->virtualType;
}






public function getChildren(): array
{
return $this->children;
}






public function setChildren(array $children): void
{
$this->children = [];
foreach ($children as $child) {
$this->addChild($child);
}
}




public function addChild(self $child): void
{
$this->children[] = $child;
$child->setParent($this);
}

public function getChildByToken(string $token): ?self
{
foreach ($this->children as $child) {
if ($token === $child->getToken()) {
return $child;
}
}

return null;
}






public function getCollector(string $name): DataCollectorInterface
{
if (!isset($this->collectors[$name])) {
throw new \InvalidArgumentException(sprintf('Collector "%s" does not exist.', $name));
}

return $this->collectors[$name];
}






public function getCollectors(): array
{
return $this->collectors;
}






public function setCollectors(array $collectors): void
{
$this->collectors = [];
foreach ($collectors as $collector) {
$this->addCollector($collector);
}
}




public function addCollector(DataCollectorInterface $collector): void
{
$this->collectors[$collector->getName()] = $collector;
}

public function hasCollector(string $name): bool
{
return isset($this->collectors[$name]);
}

public function __sleep(): array
{
return ['token', 'parent', 'children', 'collectors', 'ip', 'method', 'url', 'time', 'statusCode', 'virtualType'];
}
}
