<?php










namespace Symfony\Component\EventDispatcher;






class ImmutableEventDispatcher implements EventDispatcherInterface
{
public function __construct(
private EventDispatcherInterface $dispatcher,
) {
}

public function dispatch(object $event, ?string $eventName = null): object
{
return $this->dispatcher->dispatch($event, $eventName);
}

public function addListener(string $eventName, callable|array $listener, int $priority = 0): never
{
throw new \BadMethodCallException('Unmodifiable event dispatchers must not be modified.');
}

public function addSubscriber(EventSubscriberInterface $subscriber): never
{
throw new \BadMethodCallException('Unmodifiable event dispatchers must not be modified.');
}

public function removeListener(string $eventName, callable|array $listener): never
{
throw new \BadMethodCallException('Unmodifiable event dispatchers must not be modified.');
}

public function removeSubscriber(EventSubscriberInterface $subscriber): never
{
throw new \BadMethodCallException('Unmodifiable event dispatchers must not be modified.');
}

public function getListeners(?string $eventName = null): array
{
return $this->dispatcher->getListeners($eventName);
}

public function getListenerPriority(string $eventName, callable|array $listener): ?int
{
return $this->dispatcher->getListenerPriority($eventName, $listener);
}

public function hasListeners(?string $eventName = null): bool
{
return $this->dispatcher->hasListeners($eventName);
}
}
