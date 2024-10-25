<?php










namespace Symfony\Component\HttpKernel\Debug;

use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher as BaseTraceableEventDispatcher;
use Symfony\Component\HttpKernel\KernelEvents;








class TraceableEventDispatcher extends BaseTraceableEventDispatcher
{
protected function beforeDispatch(string $eventName, object $event): void
{
switch ($eventName) {
case KernelEvents::REQUEST:
$event->getRequest()->attributes->set('_stopwatch_token', substr(hash('xxh128', uniqid(mt_rand(), true)), 0, 6));
$this->stopwatch->openSection();
break;
case KernelEvents::VIEW:
case KernelEvents::RESPONSE:

if ($this->stopwatch->isStarted('controller')) {
$this->stopwatch->stop('controller');
}
break;
case KernelEvents::TERMINATE:
$sectionId = $event->getRequest()->attributes->get('_stopwatch_token');
if (null === $sectionId) {
break;
}





try {
$this->stopwatch->openSection($sectionId);
} catch (\LogicException) {
}
break;
}
}

protected function afterDispatch(string $eventName, object $event): void
{
switch ($eventName) {
case KernelEvents::CONTROLLER_ARGUMENTS:
$this->stopwatch->start('controller', 'section');
break;
case KernelEvents::RESPONSE:
$sectionId = $event->getRequest()->attributes->get('_stopwatch_token');
if (null === $sectionId) {
break;
}
$this->stopwatch->stopSection($sectionId);
break;
case KernelEvents::TERMINATE:


$sectionId = $event->getRequest()->attributes->get('_stopwatch_token');
if (null === $sectionId) {
break;
}
try {
$this->stopwatch->stopSection($sectionId);
} catch (\LogicException) {
}
break;
}
}
}
