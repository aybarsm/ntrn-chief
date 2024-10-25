<?php










namespace Symfony\Component\HttpKernel\Bundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;






interface BundleInterface
{





public function boot();






public function shutdown();








public function build(ContainerBuilder $container);




public function getContainerExtension(): ?ExtensionInterface;




public function getName(): string;




public function getNamespace(): string;






public function getPath(): string;

public function setContainer(?ContainerInterface $container): void;
}
