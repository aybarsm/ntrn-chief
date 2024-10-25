<?php










namespace Symfony\Component\HttpKernel\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RequestContextAwareInterface;









class RouterListener implements EventSubscriberInterface
{
private RequestContext $context;






public function __construct(
private UrlMatcherInterface|RequestMatcherInterface $matcher,
private RequestStack $requestStack,
?RequestContext $context = null,
private ?LoggerInterface $logger = null,
private ?string $projectDir = null,
private bool $debug = true,
) {
if (null === $context && !$matcher instanceof RequestContextAwareInterface) {
throw new \InvalidArgumentException('You must either pass a RequestContext or the matcher must implement RequestContextAwareInterface.');
}

$this->context = $context ?? $matcher->getContext();
}

private function setCurrentRequest(?Request $request): void
{
if (null !== $request) {
try {
$this->context->fromRequest($request);
} catch (\UnexpectedValueException $e) {
throw new BadRequestHttpException($e->getMessage(), $e, $e->getCode());
}
}
}





public function onKernelFinishRequest(): void
{
$this->setCurrentRequest($this->requestStack->getParentRequest());
}

public function onKernelRequest(RequestEvent $event): void
{
$request = $event->getRequest();

$this->setCurrentRequest($request);

if ($request->attributes->has('_controller')) {

return;
}


try {

if ($this->matcher instanceof RequestMatcherInterface) {
$parameters = $this->matcher->matchRequest($request);
} else {
$parameters = $this->matcher->match($request->getPathInfo());
}

$this->logger?->info('Matched route "{route}".', [
'route' => $parameters['_route'] ?? 'n/a',
'route_parameters' => $parameters,
'request_uri' => $request->getUri(),
'method' => $request->getMethod(),
]);

$attributes = $parameters;
if ($mapping = $parameters['_route_mapping'] ?? false) {
unset($parameters['_route_mapping']);
$mappedAttributes = [];
$attributes = [];

foreach ($parameters as $parameter => $value) {
$attribute = $mapping[$parameter] ?? $parameter;

if (!isset($mappedAttributes[$attribute])) {
$attributes[$attribute] = $value;
$mappedAttributes[$attribute] = $parameter;
} elseif ('' !== $mappedAttributes[$attribute]) {
$attributes[$attribute] = [
$mappedAttributes[$attribute] => $attributes[$attribute],
$parameter => $value,
];
$mappedAttributes[$attribute] = '';
} else {
$attributes[$attribute][$parameter] = $value;
}
}

$attributes['_route_mapping'] = $mapping;
}

$request->attributes->add($attributes);
unset($parameters['_route'], $parameters['_controller']);
$request->attributes->set('_route_params', $parameters);
} catch (ResourceNotFoundException $e) {
$message = sprintf('No route found for "%s %s"', $request->getMethod(), $request->getUriForPath($request->getPathInfo()));

if ($referer = $request->headers->get('referer')) {
$message .= sprintf(' (from "%s")', $referer);
}

throw new NotFoundHttpException($message, $e);
} catch (MethodNotAllowedException $e) {
$message = sprintf('No route found for "%s %s": Method Not Allowed (Allow: %s)', $request->getMethod(), $request->getUriForPath($request->getPathInfo()), implode(', ', $e->getAllowedMethods()));

throw new MethodNotAllowedHttpException($e->getAllowedMethods(), $message, $e);
}
}

public function onKernelException(ExceptionEvent $event): void
{
if (!$this->debug || !($e = $event->getThrowable()) instanceof NotFoundHttpException) {
return;
}

if ($e->getPrevious() instanceof NoConfigurationException) {
$event->setResponse($this->createWelcomeResponse());
}
}

public static function getSubscribedEvents(): array
{
return [
KernelEvents::REQUEST => [['onKernelRequest', 32]],
KernelEvents::FINISH_REQUEST => [['onKernelFinishRequest', 0]],
KernelEvents::EXCEPTION => ['onKernelException', -64],
];
}

private function createWelcomeResponse(): Response
{
$version = Kernel::VERSION;
$projectDir = realpath((string) $this->projectDir).\DIRECTORY_SEPARATOR;
$docVersion = substr(Kernel::VERSION, 0, 3);

ob_start();
include \dirname(__DIR__).'/Resources/welcome.html.php';

return new Response(ob_get_clean(), Response::HTTP_NOT_FOUND);
}
}
