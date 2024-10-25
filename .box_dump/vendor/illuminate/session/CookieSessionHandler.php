<?php

namespace Illuminate\Session;

use Illuminate\Contracts\Cookie\QueueingFactory as CookieJar;
use Illuminate\Support\InteractsWithTime;
use SessionHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

class CookieSessionHandler implements SessionHandlerInterface
{
use InteractsWithTime;






protected $cookie;






protected $request;






protected $minutes;






protected $expireOnClose;









public function __construct(CookieJar $cookie, $minutes, $expireOnClose = false)
{
$this->cookie = $cookie;
$this->minutes = $minutes;
$this->expireOnClose = $expireOnClose;
}






public function open($savePath, $sessionName): bool
{
return true;
}






public function close(): bool
{
return true;
}






public function read($sessionId): string|false
{
$value = $this->request->cookies->get($sessionId) ?: '';

if (! is_null($decoded = json_decode($value, true)) && is_array($decoded) &&
isset($decoded['expires']) && $this->currentTime() <= $decoded['expires']) {
return $decoded['data'];
}

return '';
}






public function write($sessionId, $data): bool
{
$this->cookie->queue($sessionId, json_encode([
'data' => $data,
'expires' => $this->availableAt($this->minutes * 60),
]), $this->expireOnClose ? 0 : $this->minutes);

return true;
}






public function destroy($sessionId): bool
{
$this->cookie->queue($this->cookie->forget($sessionId));

return true;
}






public function gc($lifetime): int
{
return 0;
}







public function setRequest(Request $request)
{
$this->request = $request;
}
}
