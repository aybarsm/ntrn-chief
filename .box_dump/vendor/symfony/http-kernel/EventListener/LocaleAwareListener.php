<?php










namespace Symfony\Component\HttpKernel\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\LocaleAwareInterface;






class LocaleAwareListener implements EventSubscriberInterface
{



public function __construct(
private iterable $localeAwareServices,
private RequestStack $requestStack,
) {
}

public function onKernelRequest(RequestEvent $event): void
{
$this->setLocale($event->getRequest()->getLocale(), $event->getRequest()->getDefaultLocale());
}

public function onKernelFinishRequest(FinishRequestEvent $event): void
{
if (null === $parentRequest = $this->requestStack->getParentRequest()) {
foreach ($this->localeAwareServices as $service) {
$service->setLocale($event->getRequest()->getDefaultLocale());
}

return;
}

$this->setLocale($parentRequest->getLocale(), $parentRequest->getDefaultLocale());
}

public static function getSubscribedEvents(): array
{
return [

KernelEvents::REQUEST => [['onKernelRequest', 15]],
KernelEvents::FINISH_REQUEST => [['onKernelFinishRequest', -15]],
];
}

private function setLocale(string $locale, string $defaultLocale): void
{
foreach ($this->localeAwareServices as $service) {
try {
$service->setLocale($locale);
} catch (\InvalidArgumentException) {
$service->setLocale($defaultLocale);
}
}
}
}
