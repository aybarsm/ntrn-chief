<?php










namespace Symfony\Component\Mime\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;






class AddMimeTypeGuesserPass implements CompilerPassInterface
{
public function process(ContainerBuilder $container): void
{
if ($container->has('mime_types')) {
$definition = $container->findDefinition('mime_types');
foreach ($container->findTaggedServiceIds('mime.mime_type_guesser', true) as $id => $attributes) {
$definition->addMethodCall('registerGuesser', [new Reference($id)]);
}
}
}
}
