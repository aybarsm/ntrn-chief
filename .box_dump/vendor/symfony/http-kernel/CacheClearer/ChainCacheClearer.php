<?php










namespace Symfony\Component\HttpKernel\CacheClearer;








class ChainCacheClearer implements CacheClearerInterface
{



public function __construct(
private iterable $clearers = [],
) {
}

public function clear(string $cacheDir): void
{
foreach ($this->clearers as $clearer) {
$clearer->clear($cacheDir);
}
}
}
