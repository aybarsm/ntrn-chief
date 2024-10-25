<?php

namespace Illuminate\Session\Middleware;

use Closure;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class StartSession
{





protected $manager;






protected $cacheFactoryResolver;








public function __construct(SessionManager $manager, ?callable $cacheFactoryResolver = null)
{
$this->manager = $manager;
$this->cacheFactoryResolver = $cacheFactoryResolver;
}








public function handle($request, Closure $next)
{
if (! $this->sessionConfigured()) {
return $next($request);
}

$session = $this->getSession($request);

if ($this->manager->shouldBlock() ||
($request->route() instanceof Route && $request->route()->locksFor())) {
return $this->handleRequestWhileBlocking($request, $session, $next);
}

return $this->handleStatefulRequest($request, $session, $next);
}









protected function handleRequestWhileBlocking(Request $request, $session, Closure $next)
{
if (! $request->route() instanceof Route) {
return;
}

$lockFor = $request->route() && $request->route()->locksFor()
? $request->route()->locksFor()
: $this->manager->defaultRouteBlockLockSeconds();

$lock = $this->cache($this->manager->blockDriver())
->lock('session:'.$session->getId(), $lockFor)
->betweenBlockedAttemptsSleepFor(50);

try {
$lock->block(
! is_null($request->route()->waitsFor())
? $request->route()->waitsFor()
: $this->manager->defaultRouteBlockWaitSeconds()
);

return $this->handleStatefulRequest($request, $session, $next);
} finally {
$lock?->release();
}
}









protected function handleStatefulRequest(Request $request, $session, Closure $next)
{



$request->setLaravelSession(
$this->startSession($request, $session)
);

$this->collectGarbage($session);

$response = $next($request);

$this->storeCurrentUrl($request, $session);

$this->addCookieToResponse($response, $session);




$this->saveSession($request);

return $response;
}








protected function startSession(Request $request, $session)
{
return tap($session, function ($session) use ($request) {
$session->setRequestOnHandler($request);

$session->start();
});
}







public function getSession(Request $request)
{
return tap($this->manager->driver(), function ($session) use ($request) {
$session->setId($request->cookies->get($session->getName()));
});
}







protected function collectGarbage(Session $session)
{
$config = $this->manager->getSessionConfig();




if ($this->configHitsLottery($config)) {
$session->getHandler()->gc($this->getSessionLifetimeInSeconds());
}
}







protected function configHitsLottery(array $config)
{
return random_int(1, $config['lottery'][1]) <= $config['lottery'][0];
}








protected function storeCurrentUrl(Request $request, $session)
{
if ($request->isMethod('GET') &&
$request->route() instanceof Route &&
! $request->ajax() &&
! $request->prefetch() &&
! $request->isPrecognitive()) {
$session->setPreviousUrl($request->fullUrl());
}
}








protected function addCookieToResponse(Response $response, Session $session)
{
if ($this->sessionIsPersistent($config = $this->manager->getSessionConfig())) {
$response->headers->setCookie(new Cookie(
$session->getName(),
$session->getId(),
$this->getCookieExpirationDate(),
$config['path'],
$config['domain'],
$config['secure'],
$config['http_only'] ?? true,
false,
$config['same_site'] ?? null,
$config['partitioned'] ?? false
));
}
}







protected function saveSession($request)
{
if (! $request->isPrecognitive()) {
$this->manager->driver()->save();
}
}






protected function getSessionLifetimeInSeconds()
{
return ($this->manager->getSessionConfig()['lifetime'] ?? null) * 60;
}






protected function getCookieExpirationDate()
{
$config = $this->manager->getSessionConfig();

return $config['expire_on_close'] ? 0 : Date::instance(
Carbon::now()->addRealMinutes($config['lifetime'])
);
}






protected function sessionConfigured()
{
return ! is_null($this->manager->getSessionConfig()['driver'] ?? null);
}







protected function sessionIsPersistent(?array $config = null)
{
$config = $config ?: $this->manager->getSessionConfig();

return ! is_null($config['driver'] ?? null);
}







protected function cache($driver)
{
return call_user_func($this->cacheFactoryResolver)->driver($driver);
}
}
