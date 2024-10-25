<?php

namespace Illuminate\Session;

use SessionHandlerInterface;

class NullSessionHandler implements SessionHandlerInterface
{





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
return '';
}






public function write($sessionId, $data): bool
{
return true;
}






public function destroy($sessionId): bool
{
return true;
}






public function gc($lifetime): int
{
return 0;
}
}
