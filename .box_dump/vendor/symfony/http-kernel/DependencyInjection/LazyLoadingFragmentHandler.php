<?php










namespace Symfony\Component\HttpKernel\DependencyInjection;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;






class LazyLoadingFragmentHandler extends FragmentHandler
{



private array $initialized = [];

public function __construct(
private ContainerInterface $container,
RequestStack $requestStack,
bool $debug = false,
) {
parent::__construct($requestStack, [], $debug);
}

public function render(string|ControllerReference $uri, string $renderer = 'inline', array $options = []): ?string
{
if (!isset($this->initialized[$renderer]) && $this->container->has($renderer)) {
$this->addRenderer($this->container->get($renderer));
$this->initialized[$renderer] = true;
}

return parent::render($uri, $renderer, $options);
}
}
