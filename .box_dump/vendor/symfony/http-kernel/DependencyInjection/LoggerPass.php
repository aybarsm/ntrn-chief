<?php










namespace Symfony\Component\HttpKernel\DependencyInjection;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Log\Logger;






class LoggerPass implements CompilerPassInterface
{
public function process(ContainerBuilder $container): void
{
$container->setAlias(LoggerInterface::class, 'logger');

if ($container->has('logger')) {
return;
}

if ($debug = $container->getParameter('kernel.debug')) {
$debug = $container->hasParameter('kernel.runtime_mode.web')
? $container->getParameter('kernel.runtime_mode.web')
: !\in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true);
}

$container->register('logger', Logger::class)
->setArguments([null, null, null, new Reference(RequestStack::class), $debug]);
}
}
