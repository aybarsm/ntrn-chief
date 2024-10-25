<?php










namespace Symfony\Component\HttpKernel\Config;

use Symfony\Component\Config\FileLocator as BaseFileLocator;
use Symfony\Component\HttpKernel\KernelInterface;






class FileLocator extends BaseFileLocator
{
public function __construct(
private KernelInterface $kernel,
) {
parent::__construct();
}

public function locate(string $file, ?string $currentPath = null, bool $first = true): string|array
{
if (isset($file[0]) && '@' === $file[0]) {
$resource = $this->kernel->locateResource($file);

return $first ? $resource : [$resource];
}

return parent::locate($file, $currentPath, $first);
}
}
