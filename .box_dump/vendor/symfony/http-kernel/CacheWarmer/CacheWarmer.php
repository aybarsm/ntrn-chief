<?php










namespace Symfony\Component\HttpKernel\CacheWarmer;






abstract class CacheWarmer implements CacheWarmerInterface
{
protected function writeCacheFile(string $file, $content): void
{
$tmpFile = @tempnam(\dirname($file), basename($file));
if (false !== @file_put_contents($tmpFile, $content) && @rename($tmpFile, $file)) {
@chmod($file, 0666 & ~umask());

return;
}

throw new \RuntimeException(sprintf('Failed to write cache file "%s".', $file));
}
}
