<?php










namespace Symfony\Component\HttpKernel\Bundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ConfigurableExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;






abstract class AbstractBundle extends Bundle implements ConfigurableExtensionInterface
{
protected string $extensionAlias = '';

public function configure(DefinitionConfigurator $definition): void
{
}

public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
{
}

public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
{
}

public function getContainerExtension(): ?ExtensionInterface
{
if ('' === $this->extensionAlias) {
$this->extensionAlias = Container::underscore(preg_replace('/Bundle$/', '', $this->getName()));
}

return $this->extension ??= new BundleExtension($this, $this->extensionAlias);
}

public function getPath(): string
{
if (!isset($this->path)) {
$reflected = new \ReflectionObject($this);

$this->path = \dirname($reflected->getFileName(), 2);
}

return $this->path;
}
}
