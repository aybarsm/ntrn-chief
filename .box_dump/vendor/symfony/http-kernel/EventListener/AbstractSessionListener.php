<?php










namespace Symfony\Component\HttpKernel\EventListener;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionUtils;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\UnexpectedSessionUsageException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ResetInterface;














abstract class AbstractSessionListener implements EventSubscriberInterface, ResetInterface
{
public const NO_AUTO_CACHE_CONTROL_HEADER = 'Symfony-Session-NoAutoCacheControl';






public function __construct(
private ?ContainerInterface $container = null,
private bool $debug = false,
private array $sessionOptions = [],
) {
}




public function onKernelRequest(RequestEvent $event): void
{
if (!$event->isMainRequest()) {
return;
}

$request = $event->getRequest();
if (!$request->hasSession()) {
$request->setSessionFactory(function () use ($request) {

static $sess;

if (!$sess) {
$sess = $this->getSession();
$request->setSession($sess);







if ($sess && !$sess->isStarted() && \PHP_SESSION_ACTIVE !== session_status()) {
$sessionId = $sess->getId() ?: $request->cookies->get($sess->getName(), '');
$sess->setId($sessionId);
}
}

return $sess;
});
}
}




public function onKernelResponse(ResponseEvent $event): void
{
if (!$event->isMainRequest() || (!$this->container->has('initialized_session') && !$event->getRequest()->hasSession())) {
return;
}

$response = $event->getResponse();
$autoCacheControl = !$response->headers->has(self::NO_AUTO_CACHE_CONTROL_HEADER);

$response->headers->remove(self::NO_AUTO_CACHE_CONTROL_HEADER);
if (!$event->getRequest()->hasSession(true)) {
return;
}
$session = $event->getRequest()->getSession();

if ($session->isStarted()) {

























$session->save();





$sessionName = $session->getName();
$sessionId = $session->getId();
$sessionOptions = $this->getSessionOptions($this->sessionOptions);
$sessionCookiePath = $sessionOptions['cookie_path'] ?? '/';
$sessionCookieDomain = $sessionOptions['cookie_domain'] ?? null;
$sessionCookieSecure = $sessionOptions['cookie_secure'] ?? false;
$sessionCookieHttpOnly = $sessionOptions['cookie_httponly'] ?? true;
$sessionCookieSameSite = $sessionOptions['cookie_samesite'] ?? Cookie::SAMESITE_LAX;
$sessionUseCookies = $sessionOptions['use_cookies'] ?? true;

SessionUtils::popSessionCookie($sessionName, $sessionId);

if ($sessionUseCookies) {
$request = $event->getRequest();
$requestSessionCookieId = $request->cookies->get($sessionName);

$isSessionEmpty = ($session instanceof Session ? $session->isEmpty() : !$session->all()) && empty($_SESSION); 
if ($requestSessionCookieId && $isSessionEmpty) {




SessionUtils::popSessionCookie($sessionName, 'deleted');
$response->headers->clearCookie(
$sessionName,
$sessionCookiePath,
$sessionCookieDomain,
$sessionCookieSecure,
$sessionCookieHttpOnly,
$sessionCookieSameSite
);
} elseif ($sessionId !== $requestSessionCookieId && !$isSessionEmpty) {
$expire = 0;
$lifetime = $sessionOptions['cookie_lifetime'] ?? null;
if ($lifetime) {
$expire = time() + $lifetime;
}

$response->headers->setCookie(
Cookie::create(
$sessionName,
$sessionId,
$expire,
$sessionCookiePath,
$sessionCookieDomain,
$sessionCookieSecure,
$sessionCookieHttpOnly,
false,
$sessionCookieSameSite
)
);
}
}
}

if ($session instanceof Session ? 0 === $session->getUsageIndex() : !$session->isStarted()) {
return;
}

if ($autoCacheControl) {
$maxAge = $response->headers->hasCacheControlDirective('public') ? 0 : (int) $response->getMaxAge();
$response
->setExpires(new \DateTimeImmutable('+'.$maxAge.' seconds'))
->setPrivate()
->setMaxAge($maxAge)
->headers->addCacheControlDirective('must-revalidate');
}

if (!$event->getRequest()->attributes->get('_stateless', false)) {
return;
}

if ($this->debug) {
throw new UnexpectedSessionUsageException('Session was used while the request was declared stateless.');
}

if ($this->container->has('logger')) {
$this->container->get('logger')->warning('Session was used while the request was declared stateless.');
}
}




public function onSessionUsage(): void
{
if (!$this->debug) {
return;
}

if ($this->container?->has('session_collector')) {
$this->container->get('session_collector')();
}

if (!$requestStack = $this->container?->has('request_stack') ? $this->container->get('request_stack') : null) {
return;
}

$stateless = false;
$clonedRequestStack = clone $requestStack;
while (null !== ($request = $clonedRequestStack->pop()) && !$stateless) {
$stateless = $request->attributes->get('_stateless');
}

if (!$stateless) {
return;
}

if (!$session = $requestStack->getCurrentRequest()->getSession()) {
return;
}

if ($session->isStarted()) {
$session->save();
}

throw new UnexpectedSessionUsageException('Session was used while the request was declared stateless.');
}




public static function getSubscribedEvents(): array
{
return [
KernelEvents::REQUEST => ['onKernelRequest', 128],

KernelEvents::RESPONSE => ['onKernelResponse', -1000],
];
}




public function reset(): void
{
if (\PHP_SESSION_ACTIVE === session_status()) {
session_abort();
}

session_unset();
$_SESSION = [];

if (!headers_sent()) { 
session_id('');
}
}






abstract protected function getSession(): ?SessionInterface;

private function getSessionOptions(array $sessionOptions): array
{
$mergedSessionOptions = [];

foreach (session_get_cookie_params() as $key => $value) {
$mergedSessionOptions['cookie_'.$key] = $value;
}

foreach ($sessionOptions as $key => $value) {

if ('cookie_secure' === $key && 'auto' === $value) {
continue;
}
$mergedSessionOptions[$key] = $value;
}

return $mergedSessionOptions;
}
}
