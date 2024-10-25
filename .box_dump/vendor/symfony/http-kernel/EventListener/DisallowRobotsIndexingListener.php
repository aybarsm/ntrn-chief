<?php










namespace Symfony\Component\HttpKernel\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;






class DisallowRobotsIndexingListener implements EventSubscriberInterface
{
private const HEADER_NAME = 'X-Robots-Tag';

public function onResponse(ResponseEvent $event): void
{
if (!$event->getResponse()->headers->has(static::HEADER_NAME)) {
$event->getResponse()->headers->set(static::HEADER_NAME, 'noindex');
}
}

public static function getSubscribedEvents(): array
{
return [
KernelEvents::RESPONSE => ['onResponse', -255],
];
}
}
