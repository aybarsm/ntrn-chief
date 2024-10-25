<?php










namespace Symfony\Component\HttpKernel\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;






class FragmentRendererPass implements CompilerPassInterface
{
public function process(ContainerBuilder $container): void
{
if (!$container->hasDefinition('fragment.handler')) {
return;
}

$definition = $container->getDefinition('fragment.handler');
$renderers = [];
foreach ($container->findTaggedServiceIds('kernel.fragment_renderer', true) as $id => $tags) {
$def = $container->getDefinition($id);
$class = $container->getParameterBag()->resolveValue($def->getClass());

if (!$r = $container->getReflectionClass($class)) {
throw new InvalidArgumentException(sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
}
if (!$r->isSubclassOf(FragmentRendererInterface::class)) {
throw new InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, FragmentRendererInterface::class));
}

foreach ($tags as $tag) {
$renderers[$tag['alias']] = new Reference($id);
}
}

$definition->replaceArgument(0, ServiceLocatorTagPass::register($container, $renderers));
}
}