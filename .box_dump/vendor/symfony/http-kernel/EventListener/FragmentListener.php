<?php










namespace Symfony\Component\HttpKernel\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;














class FragmentListener implements EventSubscriberInterface
{



public function __construct(
private UriSigner $signer,
private string $fragmentPath = '/_fragment',
) {
}






public function onKernelRequest(RequestEvent $event): void
{
$request = $event->getRequest();

if ($this->fragmentPath !== rawurldecode($request->getPathInfo())) {
return;
}

if ($request->attributes->has('_controller')) {

$request->query->remove('_path');

return;
}

if ($event->isMainRequest()) {
$this->validateRequest($request);
}

parse_str($request->query->get('_path', ''), $attributes);
$attributes['_check_controller_is_allowed'] = true;
$request->attributes->add($attributes);
$request->attributes->set('_route_params', array_replace($request->attributes->get('_route_params', []), $attributes));
$request->query->remove('_path');
}

protected function validateRequest(Request $request): void
{

if (!$request->isMethodSafe()) {
throw new AccessDeniedHttpException();
}


if ($this->signer->checkRequest($request)) {
return;
}

throw new AccessDeniedHttpException();
}

public static function getSubscribedEvents(): array
{
return [
KernelEvents::REQUEST => [['onKernelRequest', 48]],
];
}
}
