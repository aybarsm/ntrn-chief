<?php










namespace Symfony\Component\HttpKernel\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;








class AddRequestFormatsListener implements EventSubscriberInterface
{
public function __construct(
private array $formats,
) {
}




public function onKernelRequest(RequestEvent $event): void
{
$request = $event->getRequest();
foreach ($this->formats as $format => $mimeTypes) {
$request->setFormat($format, $mimeTypes);
}
}

public static function getSubscribedEvents(): array
{
return [KernelEvents::REQUEST => ['onKernelRequest', 100]];
}
}
