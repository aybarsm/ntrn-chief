<?php










namespace Symfony\Component\HttpFoundation;








class ServerBag extends ParameterBag
{



public function getHeaders(): array
{
$headers = [];
foreach ($this->parameters as $key => $value) {
if (str_starts_with($key, 'HTTP_')) {
$headers[substr($key, 5)] = $value;
} elseif (\in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true) && '' !== $value) {
$headers[$key] = $value;
}
}

if (isset($this->parameters['PHP_AUTH_USER'])) {
$headers['PHP_AUTH_USER'] = $this->parameters['PHP_AUTH_USER'];
$headers['PHP_AUTH_PW'] = $this->parameters['PHP_AUTH_PW'] ?? '';
} else {














$authorizationHeader = null;
if (isset($this->parameters['HTTP_AUTHORIZATION'])) {
$authorizationHeader = $this->parameters['HTTP_AUTHORIZATION'];
} elseif (isset($this->parameters['REDIRECT_HTTP_AUTHORIZATION'])) {
$authorizationHeader = $this->parameters['REDIRECT_HTTP_AUTHORIZATION'];
}

if (null !== $authorizationHeader) {
if (0 === stripos($authorizationHeader, 'basic ')) {

$exploded = explode(':', base64_decode(substr($authorizationHeader, 6)), 2);
if (2 == \count($exploded)) {
[$headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']] = $exploded;
}
} elseif (empty($this->parameters['PHP_AUTH_DIGEST']) && (0 === stripos($authorizationHeader, 'digest '))) {

$headers['PHP_AUTH_DIGEST'] = $authorizationHeader;
$this->parameters['PHP_AUTH_DIGEST'] = $authorizationHeader;
} elseif (0 === stripos($authorizationHeader, 'bearer ')) {





$headers['AUTHORIZATION'] = $authorizationHeader;
}
}
}

if (isset($headers['AUTHORIZATION'])) {
return $headers;
}


if (isset($headers['PHP_AUTH_USER'])) {
$headers['AUTHORIZATION'] = 'Basic '.base64_encode($headers['PHP_AUTH_USER'].':'.($headers['PHP_AUTH_PW'] ?? ''));
} elseif (isset($headers['PHP_AUTH_DIGEST'])) {
$headers['AUTHORIZATION'] = $headers['PHP_AUTH_DIGEST'];
}

return $headers;
}
}
