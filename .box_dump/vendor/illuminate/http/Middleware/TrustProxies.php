<?php

namespace Illuminate\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrustProxies
{





protected $proxies;






protected $headers = Request::HEADER_X_FORWARDED_FOR |
Request::HEADER_X_FORWARDED_HOST |
Request::HEADER_X_FORWARDED_PORT |
Request::HEADER_X_FORWARDED_PROTO |
Request::HEADER_X_FORWARDED_PREFIX |
Request::HEADER_X_FORWARDED_AWS_ELB;






protected static $alwaysTrustProxies;






protected static $alwaysTrustHeaders;










public function handle(Request $request, Closure $next)
{
$request::setTrustedProxies([], $this->getTrustedHeaderNames());

$this->setTrustedProxyIpAddresses($request);

return $next($request);
}







protected function setTrustedProxyIpAddresses(Request $request)
{
$trustedIps = $this->proxies() ?: config('trustedproxy.proxies');

if ($trustedIps === '*' || $trustedIps === '**') {
return $this->setTrustedProxyIpAddressesToTheCallingIp($request);
}

$trustedIps = is_string($trustedIps)
? array_map('trim', explode(',', $trustedIps))
: $trustedIps;

if (is_array($trustedIps)) {
return $this->setTrustedProxyIpAddressesToSpecificIps($request, $trustedIps);
}
}








protected function setTrustedProxyIpAddressesToSpecificIps(Request $request, array $trustedIps)
{
$request->setTrustedProxies(array_reduce($trustedIps, function ($ips, $trustedIp) use ($request) {
$ips[] = $trustedIp === 'REMOTE_ADDR'
? $request->server->get('REMOTE_ADDR')
: $trustedIp;

return $ips;
}, []), $this->getTrustedHeaderNames());
}







protected function setTrustedProxyIpAddressesToTheCallingIp(Request $request)
{
$request->setTrustedProxies([$request->server->get('REMOTE_ADDR')], $this->getTrustedHeaderNames());
}






protected function getTrustedHeaderNames()
{
$headers = $this->headers();

if (is_int($headers)) {
return $headers;
}

return match ($headers) {
'HEADER_X_FORWARDED_AWS_ELB' => Request::HEADER_X_FORWARDED_AWS_ELB,
'HEADER_FORWARDED' => Request::HEADER_FORWARDED,
'HEADER_X_FORWARDED_FOR' => Request::HEADER_X_FORWARDED_FOR,
'HEADER_X_FORWARDED_HOST' => Request::HEADER_X_FORWARDED_HOST,
'HEADER_X_FORWARDED_PORT' => Request::HEADER_X_FORWARDED_PORT,
'HEADER_X_FORWARDED_PROTO' => Request::HEADER_X_FORWARDED_PROTO,
'HEADER_X_FORWARDED_PREFIX' => Request::HEADER_X_FORWARDED_PREFIX,
default => Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_PREFIX | Request::HEADER_X_FORWARDED_AWS_ELB,
};
}






protected function headers()
{
return static::$alwaysTrustHeaders ?: $this->headers;
}






protected function proxies()
{
return static::$alwaysTrustProxies ?: $this->proxies;
}







public static function at(array|string $proxies)
{
static::$alwaysTrustProxies = $proxies;
}







public static function withHeaders(int $headers)
{
static::$alwaysTrustHeaders = $headers;
}






public static function flushState()
{
static::$alwaysTrustHeaders = null;
static::$alwaysTrustProxies = null;
}
}
