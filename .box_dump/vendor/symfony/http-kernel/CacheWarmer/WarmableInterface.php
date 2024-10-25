<?php










namespace Symfony\Component\HttpKernel\CacheWarmer;






interface WarmableInterface
{








public function warmUp(string $cacheDir, ?string $buildDir = null): array;
}
