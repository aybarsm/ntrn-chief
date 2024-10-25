<?php

namespace Illuminate\Session\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Session\Middleware\AuthenticatesSessions;
use Illuminate\Http\Request;

class AuthenticateSession implements AuthenticatesSessions
{





protected $auth;






protected static $redirectToCallback;







public function __construct(AuthFactory $auth)
{
$this->auth = $auth;
}








public function handle($request, Closure $next)
{
if (! $request->hasSession() || ! $request->user() || ! $request->user()->getAuthPassword()) {
return $next($request);
}

if ($this->guard()->viaRemember()) {
$passwordHash = explode('|', $request->cookies->get($this->guard()->getRecallerName()))[2] ?? null;

if (! $passwordHash || ! hash_equals($request->user()->getAuthPassword(), $passwordHash)) {
$this->logout($request);
}
}

if (! $request->session()->has('password_hash_'.$this->auth->getDefaultDriver())) {
$this->storePasswordHashInSession($request);
}

if (! hash_equals($request->session()->get('password_hash_'.$this->auth->getDefaultDriver()), $request->user()->getAuthPassword())) {
$this->logout($request);
}

return tap($next($request), function () use ($request) {
if (! is_null($this->guard()->user())) {
$this->storePasswordHashInSession($request);
}
});
}







protected function storePasswordHashInSession($request)
{
if (! $request->user()) {
return;
}

$request->session()->put([
'password_hash_'.$this->auth->getDefaultDriver() => $request->user()->getAuthPassword(),
]);
}









protected function logout($request)
{
$this->guard()->logoutCurrentDevice();

$request->session()->flush();

throw new AuthenticationException(
'Unauthenticated.', [$this->auth->getDefaultDriver()], $this->redirectTo($request)
);
}






protected function guard()
{
return $this->auth;
}







protected function redirectTo(Request $request)
{
if (static::$redirectToCallback) {
return call_user_func(static::$redirectToCallback, $request);
}
}







public static function redirectUsing(callable $redirectToCallback)
{
static::$redirectToCallback = $redirectToCallback;
}
}
