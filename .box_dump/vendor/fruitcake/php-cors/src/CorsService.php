<?php











namespace Fruitcake\Cors;

use Fruitcake\Cors\Exceptions\InvalidOptionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
@phpstan-type
















*/
class CorsService
{

private array $allowedOrigins = [];

private array $allowedOriginsPatterns = [];

private array $allowedMethods = [];

private array $allowedHeaders = [];

private array $exposedHeaders = [];
private bool $supportsCredentials = false;
private ?int $maxAge = 0;

private bool $allowAllOrigins = false;
private bool $allowAllMethods = false;
private bool $allowAllHeaders = false;




public function __construct(array $options = [])
{
if ($options) {
$this->setOptions($options);
}
}




public function setOptions(array $options): void
{
$this->allowedOrigins = $options['allowedOrigins'] ?? $options['allowed_origins'] ?? $this->allowedOrigins;
$this->allowedOriginsPatterns =
$options['allowedOriginsPatterns'] ?? $options['allowed_origins_patterns'] ?? $this->allowedOriginsPatterns;
$this->allowedMethods = $options['allowedMethods'] ?? $options['allowed_methods'] ?? $this->allowedMethods;
$this->allowedHeaders = $options['allowedHeaders'] ?? $options['allowed_headers'] ?? $this->allowedHeaders;
$this->supportsCredentials =
$options['supportsCredentials'] ?? $options['supports_credentials'] ?? $this->supportsCredentials;

$maxAge = $this->maxAge;
if (array_key_exists('maxAge', $options)) {
$maxAge = $options['maxAge'];
} elseif (array_key_exists('max_age', $options)) {
$maxAge = $options['max_age'];
}
$this->maxAge = $maxAge === null ? null : (int)$maxAge;

$exposedHeaders = $options['exposedHeaders'] ?? $options['exposed_headers'] ?? $this->exposedHeaders;
$this->exposedHeaders = $exposedHeaders === false ? [] : $exposedHeaders;

$this->normalizeOptions();
}

private function normalizeOptions(): void
{

$this->allowedHeaders = array_map('strtolower', $this->allowedHeaders);
$this->allowedMethods = array_map('strtoupper', $this->allowedMethods);


$this->allowAllOrigins = in_array('*', $this->allowedOrigins);
$this->allowAllHeaders = in_array('*', $this->allowedHeaders);
$this->allowAllMethods = in_array('*', $this->allowedMethods);


if (!$this->allowAllOrigins) {
foreach ($this->allowedOrigins as $origin) {
if (strpos($origin, '*') !== false) {
$this->allowedOriginsPatterns[] = $this->convertWildcardToPattern($origin);
}
}
}
}








private function convertWildcardToPattern($pattern)
{
$pattern = preg_quote($pattern, '#');




$pattern = str_replace('\*', '.*', $pattern);

return '#^' . $pattern . '\z#u';
}

public function isCorsRequest(Request $request): bool
{
return $request->headers->has('Origin');
}

public function isPreflightRequest(Request $request): bool
{
return $request->getMethod() === 'OPTIONS' && $request->headers->has('Access-Control-Request-Method');
}

public function handlePreflightRequest(Request $request): Response
{
$response = new Response();

$response->setStatusCode(204);

return $this->addPreflightRequestHeaders($response, $request);
}

public function addPreflightRequestHeaders(Response $response, Request $request): Response
{
$this->configureAllowedOrigin($response, $request);

if ($response->headers->has('Access-Control-Allow-Origin')) {
$this->configureAllowCredentials($response, $request);

$this->configureAllowedMethods($response, $request);

$this->configureAllowedHeaders($response, $request);

$this->configureMaxAge($response, $request);
}

return $response;
}

public function isOriginAllowed(Request $request): bool
{
if ($this->allowAllOrigins === true) {
return true;
}

$origin = (string) $request->headers->get('Origin');

if (in_array($origin, $this->allowedOrigins)) {
return true;
}

foreach ($this->allowedOriginsPatterns as $pattern) {
if (preg_match($pattern, $origin)) {
return true;
}
}

return false;
}

public function addActualRequestHeaders(Response $response, Request $request): Response
{
$this->configureAllowedOrigin($response, $request);

if ($response->headers->has('Access-Control-Allow-Origin')) {
$this->configureAllowCredentials($response, $request);

$this->configureExposedHeaders($response, $request);
}

return $response;
}

private function configureAllowedOrigin(Response $response, Request $request): void
{
if ($this->allowAllOrigins === true && !$this->supportsCredentials) {

$response->headers->set('Access-Control-Allow-Origin', '*');
} elseif ($this->isSingleOriginAllowed()) {

$response->headers->set('Access-Control-Allow-Origin', array_values($this->allowedOrigins)[0]);
} else {

if ($this->isCorsRequest($request) && $this->isOriginAllowed($request)) {
$response->headers->set('Access-Control-Allow-Origin', (string) $request->headers->get('Origin'));
}

$this->varyHeader($response, 'Origin');
}
}

private function isSingleOriginAllowed(): bool
{
if ($this->allowAllOrigins === true || count($this->allowedOriginsPatterns) > 0) {
return false;
}

return count($this->allowedOrigins) === 1;
}

private function configureAllowedMethods(Response $response, Request $request): void
{
if ($this->allowAllMethods === true) {
$allowMethods = strtoupper((string) $request->headers->get('Access-Control-Request-Method'));
$this->varyHeader($response, 'Access-Control-Request-Method');
} else {
$allowMethods = implode(', ', $this->allowedMethods);
}

$response->headers->set('Access-Control-Allow-Methods', $allowMethods);
}

private function configureAllowedHeaders(Response $response, Request $request): void
{
if ($this->allowAllHeaders === true) {
$allowHeaders = (string) $request->headers->get('Access-Control-Request-Headers');
$this->varyHeader($response, 'Access-Control-Request-Headers');
} else {
$allowHeaders = implode(', ', $this->allowedHeaders);
}
$response->headers->set('Access-Control-Allow-Headers', $allowHeaders);
}

private function configureAllowCredentials(Response $response, Request $request): void
{
if ($this->supportsCredentials) {
$response->headers->set('Access-Control-Allow-Credentials', 'true');
}
}

private function configureExposedHeaders(Response $response, Request $request): void
{
if ($this->exposedHeaders) {
$response->headers->set('Access-Control-Expose-Headers', implode(', ', $this->exposedHeaders));
}
}

private function configureMaxAge(Response $response, Request $request): void
{
if ($this->maxAge !== null) {
$response->headers->set('Access-Control-Max-Age', (string) $this->maxAge);
}
}

public function varyHeader(Response $response, string $header): Response
{
if (!$response->headers->has('Vary')) {
$response->headers->set('Vary', $header);
} elseif (!in_array($header, explode(', ', (string) $response->headers->get('Vary')))) {
$response->headers->set('Vary', ((string) $response->headers->get('Vary')) . ', ' . $header);
}

return $response;
}
}
