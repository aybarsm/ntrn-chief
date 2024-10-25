<?php










namespace Symfony\Component\HttpKernel\HttpCache;

use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;






class SubRequestHandler
{
public static function handle(HttpKernelInterface $kernel, Request $request, int $type, bool $catch): Response
{

$trustedProxies = Request::getTrustedProxies();
$trustedHeaderSet = Request::getTrustedHeaderSet();


$remoteAddr = $request->server->get('REMOTE_ADDR');
if (!$remoteAddr || !IpUtils::checkIp($remoteAddr, $trustedProxies)) {
$trustedHeaders = [
'FORWARDED' => $trustedHeaderSet & Request::HEADER_FORWARDED,
'X_FORWARDED_FOR' => $trustedHeaderSet & Request::HEADER_X_FORWARDED_FOR,
'X_FORWARDED_HOST' => $trustedHeaderSet & Request::HEADER_X_FORWARDED_HOST,
'X_FORWARDED_PROTO' => $trustedHeaderSet & Request::HEADER_X_FORWARDED_PROTO,
'X_FORWARDED_PORT' => $trustedHeaderSet & Request::HEADER_X_FORWARDED_PORT,
'X_FORWARDED_PREFIX' => $trustedHeaderSet & Request::HEADER_X_FORWARDED_PREFIX,
];
foreach (array_filter($trustedHeaders) as $name => $key) {
$request->headers->remove($name);
$request->server->remove('HTTP_'.$name);
}
}


$trustedIps = [];
$trustedValues = [];
foreach (array_reverse($request->getClientIps()) as $ip) {
$trustedIps[] = $ip;
$trustedValues[] = sprintf('for="%s"', $ip);
}
if ($ip !== $remoteAddr) {
$trustedIps[] = $remoteAddr;
$trustedValues[] = sprintf('for="%s"', $remoteAddr);
}


if (Request::HEADER_FORWARDED & $trustedHeaderSet) {
$trustedValues[0] .= sprintf(';host="%s";proto=%s', $request->getHttpHost(), $request->getScheme());
$request->headers->set('Forwarded', $v = implode(', ', $trustedValues));
$request->server->set('HTTP_FORWARDED', $v);
}
if (Request::HEADER_X_FORWARDED_FOR & $trustedHeaderSet) {
$request->headers->set('X-Forwarded-For', $v = implode(', ', $trustedIps));
$request->server->set('HTTP_X_FORWARDED_FOR', $v);
} elseif (!(Request::HEADER_FORWARDED & $trustedHeaderSet)) {
Request::setTrustedProxies($trustedProxies, $trustedHeaderSet | Request::HEADER_X_FORWARDED_FOR);
$request->headers->set('X-Forwarded-For', $v = implode(', ', $trustedIps));
$request->server->set('HTTP_X_FORWARDED_FOR', $v);
}



$request->server->set('REMOTE_ADDR', '127.0.0.1');


if (!IpUtils::checkIp('127.0.0.1', $trustedProxies)) {
Request::setTrustedProxies(array_merge($trustedProxies, ['127.0.0.1']), Request::getTrustedHeaderSet());
}

try {
return $kernel->handle($request, $type, $catch);
} finally {

Request::setTrustedProxies($trustedProxies, $trustedHeaderSet);
}
}
}
