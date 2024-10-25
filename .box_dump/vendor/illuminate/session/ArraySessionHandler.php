<?php

namespace Illuminate\Session;

use Illuminate\Support\InteractsWithTime;
use SessionHandlerInterface;

class ArraySessionHandler implements SessionHandlerInterface
{
use InteractsWithTime;






protected $storage = [];






protected $minutes;







public function __construct($minutes)
{
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






public function read($sessionId): string|false
{
if (! isset($this->storage[$sessionId])) {
return '';
}

$session = $this->storage[$sessionId];

$expiration = $this->calculateExpiration($this->minutes * 60);

if (isset($session['time']) && $session['time'] >= $expiration) {
return $session['data'];
}

return '';
}






public function write($sessionId, $data): bool
{
$this->storage[$sessionId] = [
'data' => $data,
'time' => $this->currentTime(),
];

return true;
}






public function destroy($sessionId): bool
{
if (isset($this->storage[$sessionId])) {
unset($this->storage[$sessionId]);
}

return true;
}






public function gc($lifetime): int
{
$expiration = $this->calculateExpiration($lifetime);

$deletedSessions = 0;

foreach ($this->storage as $sessionId => $session) {
if ($session['time'] < $expiration) {
unset($this->storage[$sessionId]);
$deletedSessions++;
}
}

return $deletedSessions;
}







protected function calculateExpiration($seconds)
{
return $this->currentTime() - $seconds;
}
}
