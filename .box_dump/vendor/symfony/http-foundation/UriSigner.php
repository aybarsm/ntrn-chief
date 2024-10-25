<?php










namespace Symfony\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Exception\LogicException;




class UriSigner
{
private string $secret;
private string $hashParameter;
private string $expirationParameter;





public function __construct(#[\SensitiveParameter] string $secret, string $hashParameter = '_hash', string $expirationParameter = '_expiration')
{
if (!$secret) {
throw new \InvalidArgumentException('A non-empty secret is required.');
}

$this->secret = $secret;
$this->hashParameter = $hashParameter;
$this->expirationParameter = $expirationParameter;
}















public function sign(string $uri): string
{
$expiration = null;

if (1 < \func_num_args()) {
$expiration = func_get_arg(1);
}

if (null !== $expiration && !$expiration instanceof \DateTimeInterface && !$expiration instanceof \DateInterval && !\is_int($expiration)) {
throw new \TypeError(sprintf('The second argument of %s() must be an instance of %s or %s, an integer or null (%s given).', __METHOD__, \DateTimeInterface::class, \DateInterval::class, get_debug_type($expiration)));
}

$url = parse_url($uri);
$params = [];

if (isset($url['query'])) {
parse_str($url['query'], $params);
}

if (isset($params[$this->hashParameter])) {
throw new LogicException(sprintf('URI query parameter conflict: parameter name "%s" is reserved.', $this->hashParameter));
}

if (isset($params[$this->expirationParameter])) {
throw new LogicException(sprintf('URI query parameter conflict: parameter name "%s" is reserved.', $this->expirationParameter));
}

if (null !== $expiration) {
$params[$this->expirationParameter] = $this->getExpirationTime($expiration);
}

$uri = $this->buildUrl($url, $params);
$params[$this->hashParameter] = $this->computeHash($uri);

return $this->buildUrl($url, $params);
}





public function check(string $uri): bool
{
$url = parse_url($uri);
$params = [];

if (isset($url['query'])) {
parse_str($url['query'], $params);
}

if (empty($params[$this->hashParameter])) {
return false;
}

$hash = $params[$this->hashParameter];
unset($params[$this->hashParameter]);

if (!hash_equals($this->computeHash($this->buildUrl($url, $params)), $hash)) {
return false;
}

if ($expiration = $params[$this->expirationParameter] ?? false) {
return time() < $expiration;
}

return true;
}

public function checkRequest(Request $request): bool
{
$qs = ($qs = $request->server->get('QUERY_STRING')) ? '?'.$qs : '';


return $this->check($request->getSchemeAndHttpHost().$request->getBaseUrl().$request->getPathInfo().$qs);
}

private function computeHash(string $uri): string
{
return base64_encode(hash_hmac('sha256', $uri, $this->secret, true));
}

private function buildUrl(array $url, array $params = []): string
{
ksort($params, \SORT_STRING);
$url['query'] = http_build_query($params, '', '&');

$scheme = isset($url['scheme']) ? $url['scheme'].'://' : '';
$host = $url['host'] ?? '';
$port = isset($url['port']) ? ':'.$url['port'] : '';
$user = $url['user'] ?? '';
$pass = isset($url['pass']) ? ':'.$url['pass'] : '';
$pass = ($user || $pass) ? "$pass@" : '';
$path = $url['path'] ?? '';
$query = $url['query'] ? '?'.$url['query'] : '';
$fragment = isset($url['fragment']) ? '#'.$url['fragment'] : '';

return $scheme.$user.$pass.$host.$port.$path.$query.$fragment;
}

private function getExpirationTime(\DateTimeInterface|\DateInterval|int $expiration): string
{
if ($expiration instanceof \DateTimeInterface) {
return $expiration->format('U');
}

if ($expiration instanceof \DateInterval) {
return \DateTimeImmutable::createFromFormat('U', time())->add($expiration)->format('U');
}

return (string) $expiration;
}
}
