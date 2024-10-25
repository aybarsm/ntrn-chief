<?php

namespace Illuminate\Session;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use SessionHandlerInterface;
use Symfony\Component\Finder\Finder;

class FileSessionHandler implements SessionHandlerInterface
{





protected $files;






protected $path;






protected $minutes;









public function __construct(Filesystem $files, $path, $minutes)
{
$this->path = $path;
$this->files = $files;
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
if ($this->files->isFile($path = $this->path.'/'.$sessionId) &&
$this->files->lastModified($path) >= Carbon::now()->subMinutes($this->minutes)->getTimestamp()) {
return $this->files->sharedGet($path);
}

return '';
}






public function write($sessionId, $data): bool
{
$this->files->put($this->path.'/'.$sessionId, $data, true);

return true;
}






public function destroy($sessionId): bool
{
$this->files->delete($this->path.'/'.$sessionId);

return true;
}






public function gc($lifetime): int
{
$files = Finder::create()
->in($this->path)
->files()
->ignoreDotFiles(true)
->date('<= now - '.$lifetime.' seconds');

$deletedSessions = 0;

foreach ($files as $file) {
$this->files->delete($file->getRealPath());
$deletedSessions++;
}

return $deletedSessions;
}
}
