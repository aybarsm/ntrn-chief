<?php










namespace Symfony\Component\HttpKernel\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;








class ValidateRequestListener implements EventSubscriberInterface
{



public function onKernelRequest(RequestEvent $event): void
{
if (!$event->isMainRequest()) {
return;
}
$request = $event->getRequest();

if ($request::getTrustedProxies()) {
$request->getClientIps();
}

$request->getHost();
}

public static function getSubscribedEvents(): array
{
return [
KernelEvents::REQUEST => [
['onKernelRequest', 256],
],
];
}
}
