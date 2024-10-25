<?php










namespace Symfony\Component\HttpKernel\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;








class ResponseListener implements EventSubscriberInterface
{
public function __construct(
private string $charset,
private bool $addContentLanguageHeader = false,
) {
}




public function onKernelResponse(ResponseEvent $event): void
{
if (!$event->isMainRequest()) {
return;
}

$response = $event->getResponse();

if (null === $response->getCharset()) {
$response->setCharset($this->charset);
}

if ($this->addContentLanguageHeader && !$response->isInformational() && !$response->isEmpty() && !$response->headers->has('Content-Language')) {
$response->headers->set('Content-Language', $event->getRequest()->getLocale());
}

if ($event->getRequest()->attributes->get('_vary_by_language')) {
$response->setVary('Accept-Language', false);
}

$response->prepare($event->getRequest());
}

public static function getSubscribedEvents(): array
{
return [
KernelEvents::RESPONSE => 'onKernelResponse',
];
}
}
