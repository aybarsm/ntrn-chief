<?php










namespace Symfony\Component\HttpKernel\CacheClearer;






interface CacheClearerInterface
{



public function clear(string $cacheDir): void;
}
