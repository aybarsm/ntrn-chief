<?php










namespace Symfony\Component\HttpKernel\DependencyInjection;

use ProxyManager\Proxy\LazyLoadingInterface;
use Symfony\Component\VarExporter\LazyObjectInterface;
use Symfony\Contracts\Service\ResetInterface;









class ServicesResetter implements ResetInterface
{




public function __construct(
private \Traversable $resettableServices,
private array $resetMethods,
) {
}

public function reset(): void
{
foreach ($this->resettableServices as $id => $service) {
if ($service instanceof LazyObjectInterface && !$service->isLazyObjectInitialized(true)) {
continue;
}

if ($service instanceof LazyLoadingInterface && !$service->isProxyInitialized()) {
continue;
}

foreach ((array) $this->resetMethods[$id] as $resetMethod) {
if ('?' === $resetMethod[0] && !method_exists($service, $resetMethod = substr($resetMethod, 1))) {
continue;
}

$service->$resetMethod();
}
}
}
}
