<?php










namespace Symfony\Component\HttpKernel\Bundle;

use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;






abstract class Bundle implements BundleInterface
{
protected string $name;
protected ExtensionInterface|false|null $extension = null;
protected string $path;
protected ?ContainerInterface $container;

private string $namespace;




public function boot()
{
}




public function shutdown()
{
}







public function build(ContainerBuilder $container)
{
}






public function getContainerExtension(): ?ExtensionInterface
{
if (!isset($this->extension)) {
$extension = $this->createContainerExtension();

if (null !== $extension) {
if (!$extension instanceof ExtensionInterface) {
throw new \LogicException(sprintf('Extension "%s" must implement Symfony\Component\DependencyInjection\Extension\ExtensionInterface.', get_debug_type($extension)));
}


$basename = preg_replace('/Bundle$/', '', $this->getName());
$expectedAlias = Container::underscore($basename);

if ($expectedAlias != $extension->getAlias()) {
throw new \LogicException(sprintf('Users will expect the alias of the default extension of a bundle to be the underscored version of the bundle name ("%s"). You can override "Bundle::getContainerExtension()" if you want to use "%s" or another alias.', $expectedAlias, $extension->getAlias()));
}

$this->extension = $extension;
} else {
$this->extension = false;
}
}

return $this->extension ?: null;
}

public function getNamespace(): string
{
if (!isset($this->namespace)) {
$this->parseClassName();
}

return $this->namespace;
}

public function getPath(): string
{
if (!isset($this->path)) {
$reflected = new \ReflectionObject($this);
$this->path = \dirname($reflected->getFileName());
}

return $this->path;
}




final public function getName(): string
{
if (!isset($this->name)) {
$this->parseClassName();
}

return $this->name;
}




public function registerCommands(Application $application)
{
}




protected function getContainerExtensionClass(): string
{
$basename = preg_replace('/Bundle$/', '', $this->getName());

return $this->getNamespace().'\\DependencyInjection\\'.$basename.'Extension';
}




protected function createContainerExtension(): ?ExtensionInterface
{
return class_exists($class = $this->getContainerExtensionClass()) ? new $class() : null;
}

private function parseClassName(): void
{
$pos = strrpos(static::class, '\\');
$this->namespace = false === $pos ? '' : substr(static::class, 0, $pos);
$this->name ??= false === $pos ? static::class : substr(static::class, $pos + 1);
}

public function setContainer(?ContainerInterface $container): void
{
$this->container = $container;
}
}
