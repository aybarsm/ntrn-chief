<?php










namespace Symfony\Component\HttpKernel\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\SurrogateInterface;
use Symfony\Component\HttpKernel\KernelEvents;








class SurrogateListener implements EventSubscriberInterface
{
public function __construct(
private ?SurrogateInterface $surrogate = null,
) {
}




public function onKernelResponse(ResponseEvent $event): void
{
if (!$event->isMainRequest()) {
return;
}

$kernel = $event->getKernel();
$surrogate = $this->surrogate;
if ($kernel instanceof HttpCache) {
$surrogate = $kernel->getSurrogate();
if (null !== $this->surrogate && $this->surrogate->getName() !== $surrogate->getName()) {
$surrogate = $this->surrogate;
}
}

if (null === $surrogate) {
return;
}

$surrogate->addSurrogateControl($event->getResponse());
}

public static function getSubscribedEvents(): array
{
return [
KernelEvents::RESPONSE => 'onKernelResponse',
];
}
}
