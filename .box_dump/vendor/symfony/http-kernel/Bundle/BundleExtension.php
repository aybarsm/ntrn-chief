<?php










namespace Symfony\Component\HttpKernel\Bundle;

use Symfony\Component\Config\Definition\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ConfigurableExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionTrait;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;






class BundleExtension extends Extension implements PrependExtensionInterface
{
use ExtensionTrait;

public function __construct(
private ConfigurableExtensionInterface $subject,
private string $alias,
) {
}

public function getConfiguration(array $config, ContainerBuilder $container): ?ConfigurationInterface
{
return new Configuration($this->subject, $container, $this->getAlias());
}

public function getAlias(): string
{
return $this->alias;
}

public function prepend(ContainerBuilder $container): void
{
$callback = function (ContainerConfigurator $configurator) use ($container) {
$this->subject->prependExtension($configurator, $container);
};

$this->executeConfiguratorCallback($container, $callback, $this->subject, true);
}

public function load(array $configs, ContainerBuilder $container): void
{
$config = $this->processConfiguration($this->getConfiguration([], $container), $configs);

$callback = function (ContainerConfigurator $configurator) use ($config, $container) {
$this->subject->loadExtension($config, $configurator, $container);
};

$this->executeConfiguratorCallback($container, $callback, $this->subject);
}
}
