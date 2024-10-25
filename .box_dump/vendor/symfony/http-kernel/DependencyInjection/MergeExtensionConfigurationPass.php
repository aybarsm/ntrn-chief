<?php










namespace Symfony\Component\HttpKernel\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationPass as BaseMergeExtensionConfigurationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;






class MergeExtensionConfigurationPass extends BaseMergeExtensionConfigurationPass
{



public function __construct(
private array $extensions,
) {
}

public function process(ContainerBuilder $container): void
{
foreach ($this->extensions as $extension) {
if (!\count($container->getExtensionConfig($extension))) {
$container->loadFromExtension($extension, []);
}
}

parent::process($container);
}
}
