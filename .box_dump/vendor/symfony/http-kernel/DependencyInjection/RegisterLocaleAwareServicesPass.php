<?php










namespace Symfony\Component\HttpKernel\DependencyInjection;

use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;






class RegisterLocaleAwareServicesPass implements CompilerPassInterface
{
public function process(ContainerBuilder $container): void
{
if (!$container->hasDefinition('locale_aware_listener')) {
return;
}

$services = [];

foreach ($container->findTaggedServiceIds('kernel.locale_aware') as $id => $tags) {
$services[] = new Reference($id);
}

if (!$services) {
$container->removeDefinition('locale_aware_listener');

return;
}

$container
->getDefinition('locale_aware_listener')
->setArgument(0, new IteratorArgument($services))
;
}
}
