<?php










namespace Symfony\Component\HttpKernel\EventListener;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;








class SessionListener extends AbstractSessionListener
{
public function __construct(
private ?ContainerInterface $container = null,
bool $debug = false,
array $sessionOptions = [],
) {
parent::__construct($container, $debug, $sessionOptions);
}

protected function getSession(): ?SessionInterface
{
if ($this->container->has('session_factory')) {
return $this->container->get('session_factory')->createSession();
}

return null;
}
}
