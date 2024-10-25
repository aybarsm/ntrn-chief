<?php










namespace Symfony\Component\HttpKernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;








interface KernelInterface extends HttpKernelInterface
{





public function registerBundles(): iterable;






public function registerContainerConfiguration(LoaderInterface $loader);






public function boot();








public function shutdown();






public function getBundles(): array;






public function getBundle(string $name): BundleInterface;
















public function locateResource(string $name): string;




public function getEnvironment(): string;




public function isDebug(): bool;




public function getProjectDir(): string;




public function getContainer(): ContainerInterface;




public function getStartTime(): float;








public function getCacheDir(): string;







public function getBuildDir(): string;




public function getLogDir(): string;




public function getCharset(): string;
}
