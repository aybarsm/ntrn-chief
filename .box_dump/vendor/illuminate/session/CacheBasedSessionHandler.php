<?php

namespace Illuminate\Session;

use Illuminate\Contracts\Cache\Repository as CacheContract;
use SessionHandlerInterface;

class CacheBasedSessionHandler implements SessionHandlerInterface
{





protected $cache;






protected $minutes;








public function __construct(CacheContract $cache, $minutes)
{
$this->cache = $cache;
$this->minutes = $minutes;
}






public function open($savePath, $sessionName): bool
{
return true;
}






public function close(): bool
{
return true;
}






public function read($sessionId): string
{
return $this->cache->get($sessionId, '');
}






public function write($sessionId, $data): bool
{
return $this->cache->put($sessionId, $data, $this->minutes * 60);
}






public function destroy($sessionId): bool
{
return $this->cache->forget($sessionId);
}






public function gc($lifetime): int
{
return 0;
}






public function getCache()
{
return $this->cache;
}
}
