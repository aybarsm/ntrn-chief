<?php










namespace Symfony\Component\HttpKernel\CacheClearer;

use Psr\Cache\CacheItemPoolInterface;




class Psr6CacheClearer implements CacheClearerInterface
{
private array $pools = [];




public function __construct(array $pools = [])
{
$this->pools = $pools;
}

public function hasPool(string $name): bool
{
return isset($this->pools[$name]);
}




public function getPool(string $name): CacheItemPoolInterface
{
if (!$this->hasPool($name)) {
throw new \InvalidArgumentException(sprintf('Cache pool not found: "%s".', $name));
}

return $this->pools[$name];
}




public function clearPool(string $name): bool
{
if (!isset($this->pools[$name])) {
throw new \InvalidArgumentException(sprintf('Cache pool not found: "%s".', $name));
}

return $this->pools[$name]->clear();
}

public function clear(string $cacheDir): void
{
foreach ($this->pools as $pool) {
$pool->clear();
}
}
}
