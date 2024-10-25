<?php

namespace Illuminate\Testing;

use ArrayAccess;
use Closure;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Dumpable;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\Tappable;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Testing\Constraints\SeeInOrder;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponseAssert as PHPUnit;
use LogicException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
@template
@mixin

*/
class TestResponse implements ArrayAccess
{
use Concerns\AssertsStatusCodes, Conditionable, Dumpable, Tappable, Macroable {
__call as macroCall;
}






public $baseRequest;






public $baseResponse;






public $exceptions;






protected $streamedContent;








public function __construct($response, $request = null)
{
$this->baseResponse = $response;
$this->baseRequest = $request;
$this->exceptions = new Collection;
}

/**
@template






*/
public static function fromBaseResponse($response, $request = null)
{
return new static($response, $request);
}






public function assertSuccessful()
{
PHPUnit::withResponse($this)->assertTrue(
$this->isSuccessful(),
$this->statusMessageWithDetails('>=200, <300', $this->getStatusCode())
);

return $this;
}






public function assertSuccessfulPrecognition()
{
$this->assertNoContent();

PHPUnit::withResponse($this)->assertTrue(
$this->headers->has('Precognition-Success'),
'Header [Precognition-Success] not present on response.'
);

PHPUnit::withResponse($this)->assertSame(
'true',
$this->headers->get('Precognition-Success'),
'The Precognition-Success header was found, but the value is not `true`.'
);

return $this;
}






public function assertServerError()
{
PHPUnit::withResponse($this)->assertTrue(
$this->isServerError(),
$this->statusMessageWithDetails('>=500, < 600', $this->getStatusCode())
);

return $this;
}







public function assertStatus($status)
{
$message = $this->statusMessageWithDetails($status, $actual = $this->getStatusCode());

PHPUnit::withResponse($this)->assertSame($status, $actual, $message);

return $this;
}








protected function statusMessageWithDetails($expected, $actual)
{
return "Expected response status code [{$expected}] but received {$actual}.";
}







public function assertRedirect($uri = null)
{
PHPUnit::withResponse($this)->assertTrue(
$this->isRedirect(),
$this->statusMessageWithDetails('201, 301, 302, 303, 307, 308', $this->getStatusCode()),
);

if (! is_null($uri)) {
$this->assertLocation($uri);
}

return $this;
}







public function assertRedirectContains($uri)
{
PHPUnit::withResponse($this)->assertTrue(
$this->isRedirect(),
$this->statusMessageWithDetails('201, 301, 302, 303, 307, 308', $this->getStatusCode()),
);

PHPUnit::withResponse($this)->assertTrue(
Str::contains($this->headers->get('Location'), $uri), 'Redirect location ['.$this->headers->get('Location').'] does not contain ['.$uri.'].'
);

return $this;
}








public function assertRedirectToRoute($name, $parameters = [])
{
$uri = route($name, $parameters);

PHPUnit::withResponse($this)->assertTrue(
$this->isRedirect(),
$this->statusMessageWithDetails('201, 301, 302, 303, 307, 308', $this->getStatusCode()),
);

$this->assertLocation($uri);

return $this;
}









public function assertRedirectToSignedRoute($name = null, $parameters = [], $absolute = true)
{
if (! is_null($name)) {
$uri = route($name, $parameters);
}

PHPUnit::withResponse($this)->assertTrue(
$this->isRedirect(),
$this->statusMessageWithDetails('201, 301, 302, 303, 307, 308', $this->getStatusCode()),
);

$request = Request::create($this->headers->get('Location'));

PHPUnit::withResponse($this)->assertTrue(
$request->hasValidSignature($absolute), 'The response is not a redirect to a signed route.'
);

if (! is_null($name)) {
$expectedUri = rtrim($request->fullUrlWithQuery([
'signature' => null,
'expires' => null,
]), '?');

PHPUnit::withResponse($this)->assertEquals(
app('url')->to($uri), $expectedUri
);
}

return $this;
}








public function assertHeader($headerName, $value = null)
{
PHPUnit::withResponse($this)->assertTrue(
$this->headers->has($headerName), "Header [{$headerName}] not present on response."
);

$actual = $this->headers->get($headerName);

if (! is_null($value)) {
PHPUnit::withResponse($this)->assertEquals(
$value, $this->headers->get($headerName),
"Header [{$headerName}] was found, but value [{$actual}] does not match [{$value}]."
);
}

return $this;
}







public function assertHeaderMissing($headerName)
{
PHPUnit::withResponse($this)->assertFalse(
$this->headers->has($headerName), "Unexpected header [{$headerName}] is present on response."
);

return $this;
}







public function assertLocation($uri)
{
PHPUnit::withResponse($this)->assertEquals(
app('url')->to($uri), app('url')->to($this->headers->get('Location', ''))
);

return $this;
}







public function assertDownload($filename = null)
{
$contentDisposition = explode(';', $this->headers->get('content-disposition', ''));

if (trim($contentDisposition[0]) !== 'attachment') {
PHPUnit::withResponse($this)->fail(
'Response does not offer a file download.'.PHP_EOL.
'Disposition ['.trim($contentDisposition[0]).'] found in header, [attachment] expected.'
);
}

if (! is_null($filename)) {
if (isset($contentDisposition[1]) &&
trim(explode('=', $contentDisposition[1])[0]) !== 'filename') {
PHPUnit::withResponse($this)->fail(
'Unsupported Content-Disposition header provided.'.PHP_EOL.
'Disposition ['.trim(explode('=', $contentDisposition[1])[0]).'] found in header, [filename] expected.'
);
}

$message = "Expected file [{$filename}] is not present in Content-Disposition header.";

if (! isset($contentDisposition[1])) {
PHPUnit::withResponse($this)->fail($message);
} else {
PHPUnit::withResponse($this)->assertSame(
$filename,
isset(explode('=', $contentDisposition[1])[1])
? trim(explode('=', $contentDisposition[1])[1], " \"'")
: '',
$message
);

return $this;
}
} else {
PHPUnit::withResponse($this)->assertTrue(true);

return $this;
}
}








public function assertPlainCookie($cookieName, $value = null)
{
$this->assertCookie($cookieName, $value, false);

return $this;
}










public function assertCookie($cookieName, $value = null, $encrypted = true, $unserialize = false)
{
PHPUnit::withResponse($this)->assertNotNull(
$cookie = $this->getCookie($cookieName, $encrypted && ! is_null($value), $unserialize),
"Cookie [{$cookieName}] not present on response."
);

if (! $cookie || is_null($value)) {
return $this;
}

$cookieValue = $cookie->getValue();

PHPUnit::withResponse($this)->assertEquals(
$value, $cookieValue,
"Cookie [{$cookieName}] was found, but value [{$cookieValue}] does not match [{$value}]."
);

return $this;
}







public function assertCookieExpired($cookieName)
{
PHPUnit::withResponse($this)->assertNotNull(
$cookie = $this->getCookie($cookieName, false),
"Cookie [{$cookieName}] not present on response."
);

$expiresAt = Carbon::createFromTimestamp($cookie->getExpiresTime(), date_default_timezone_get());

PHPUnit::withResponse($this)->assertTrue(
$cookie->getExpiresTime() !== 0 && $expiresAt->lessThan(Carbon::now()),
"Cookie [{$cookieName}] is not expired, it expires at [{$expiresAt}]."
);

return $this;
}







public function assertCookieNotExpired($cookieName)
{
PHPUnit::withResponse($this)->assertNotNull(
$cookie = $this->getCookie($cookieName, false),
"Cookie [{$cookieName}] not present on response."
);

$expiresAt = Carbon::createFromTimestamp($cookie->getExpiresTime(), date_default_timezone_get());

PHPUnit::withResponse($this)->assertTrue(
$cookie->getExpiresTime() === 0 || $expiresAt->greaterThan(Carbon::now()),
"Cookie [{$cookieName}] is expired, it expired at [{$expiresAt}]."
);

return $this;
}







public function assertCookieMissing($cookieName)
{
PHPUnit::withResponse($this)->assertNull(
$this->getCookie($cookieName, false),
"Cookie [{$cookieName}] is present on response."
);

return $this;
}









public function getCookie($cookieName, $decrypt = true, $unserialize = false)
{
foreach ($this->headers->getCookies() as $cookie) {
if ($cookie->getName() === $cookieName) {
if (! $decrypt) {
return $cookie;
}

$decryptedValue = CookieValuePrefix::remove(
app('encrypter')->decrypt($cookie->getValue(), $unserialize)
);

return new Cookie(
$cookie->getName(),
$decryptedValue,
$cookie->getExpiresTime(),
$cookie->getPath(),
$cookie->getDomain(),
$cookie->isSecure(),
$cookie->isHttpOnly(),
$cookie->isRaw(),
$cookie->getSameSite(),
$cookie->isPartitioned()
);
}
}
}







public function assertContent($value)
{
PHPUnit::withResponse($this)->assertSame($value, $this->content());

return $this;
}







public function assertStreamedContent($value)
{
PHPUnit::withResponse($this)->assertSame($value, $this->streamedContent());

return $this;
}







public function assertStreamedJsonContent($value)
{
return $this->assertStreamedContent(json_encode($value, JSON_THROW_ON_ERROR));
}








public function assertSee($value, $escape = true)
{
$value = Arr::wrap($value);

$values = $escape ? array_map('e', $value) : $value;

foreach ($values as $value) {
PHPUnit::withResponse($this)->assertStringContainsString((string) $value, $this->getContent());
}

return $this;
}







public function assertSeeHtml($value)
{
return $this->assertSee($value, false);
}








public function assertSeeInOrder(array $values, $escape = true)
{
$values = $escape ? array_map('e', $values) : $values;

PHPUnit::withResponse($this)->assertThat($values, new SeeInOrder($this->getContent()));

return $this;
}







public function assertSeeHtmlInOrder(array $values)
{
return $this->assertSeeInOrder($values, false);
}








public function assertSeeText($value, $escape = true)
{
$value = Arr::wrap($value);

$values = $escape ? array_map('e', $value) : $value;

$content = strip_tags($this->getContent());

foreach ($values as $value) {
PHPUnit::withResponse($this)->assertStringContainsString((string) $value, $content);
}

return $this;
}








public function assertSeeTextInOrder(array $values, $escape = true)
{
$values = $escape ? array_map('e', $values) : $values;

PHPUnit::withResponse($this)->assertThat($values, new SeeInOrder(strip_tags($this->getContent())));

return $this;
}








public function assertDontSee($value, $escape = true)
{
$value = Arr::wrap($value);

$values = $escape ? array_map('e', $value) : $value;

foreach ($values as $value) {
PHPUnit::withResponse($this)->assertStringNotContainsString((string) $value, $this->getContent());
}

return $this;
}







public function assertDontSeeHtml($value)
{
return $this->assertDontSee($value, false);
}








public function assertDontSeeText($value, $escape = true)
{
$value = Arr::wrap($value);

$values = $escape ? array_map('e', $value) : $value;

$content = strip_tags($this->getContent());

foreach ($values as $value) {
PHPUnit::withResponse($this)->assertStringNotContainsString((string) $value, $content);
}

return $this;
}








public function assertJson($value, $strict = false)
{
$json = $this->decodeResponseJson();

if (is_array($value)) {
$json->assertSubset($value, $strict);
} else {
$assert = AssertableJson::fromAssertableJsonString($json);

$value($assert);

if (Arr::isAssoc($assert->toArray())) {
$assert->interacted();
}
}

return $this;
}








public function assertJsonPath($path, $expect)
{
$this->decodeResponseJson()->assertPath($path, $expect);

return $this;
}








public function assertJsonPathCanonicalizing($path, array $expect)
{
$this->decodeResponseJson()->assertPathCanonicalizing($path, $expect);

return $this;
}







public function assertExactJson(array $data)
{
$this->decodeResponseJson()->assertExact($data);

return $this;
}







public function assertSimilarJson(array $data)
{
$this->decodeResponseJson()->assertSimilar($data);

return $this;
}







public function assertJsonFragment(array $data)
{
$this->decodeResponseJson()->assertFragment($data);

return $this;
}








public function assertJsonMissing(array $data, $exact = false)
{
$this->decodeResponseJson()->assertMissing($data, $exact);

return $this;
}







public function assertJsonMissingExact(array $data)
{
$this->decodeResponseJson()->assertMissingExact($data);

return $this;
}







public function assertJsonMissingPath(string $path)
{
$this->decodeResponseJson()->assertMissingPath($path);

return $this;
}








public function assertJsonStructure(?array $structure = null, $responseData = null)
{
$this->decodeResponseJson()->assertStructure($structure, $responseData);

return $this;
}








public function assertExactJsonStructure(?array $structure = null, $responseData = null)
{
$this->decodeResponseJson()->assertStructure($structure, $responseData, true);

return $this;
}








public function assertJsonCount(int $count, $key = null)
{
$this->decodeResponseJson()->assertCount($count, $key);

return $this;
}








public function assertJsonValidationErrors($errors, $responseKey = 'errors')
{
$errors = Arr::wrap($errors);

PHPUnit::withResponse($this)->assertNotEmpty($errors, 'No validation errors were provided.');

$jsonErrors = Arr::get($this->json(), $responseKey) ?? [];

$errorMessage = $jsonErrors
? 'Response has the following JSON validation errors:'.
PHP_EOL.PHP_EOL.json_encode($jsonErrors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL
: 'Response does not have JSON validation errors.';

foreach ($errors as $key => $value) {
if (is_int($key)) {
$this->assertJsonValidationErrorFor($value, $responseKey);

continue;
}

$this->assertJsonValidationErrorFor($key, $responseKey);

foreach (Arr::wrap($value) as $expectedMessage) {
$errorMissing = true;

foreach (Arr::wrap($jsonErrors[$key]) as $jsonErrorMessage) {
if (Str::contains($jsonErrorMessage, $expectedMessage)) {
$errorMissing = false;

break;
}
}

if ($errorMissing) {
PHPUnit::withResponse($this)->fail(
"Failed to find a validation error in the response for key and message: '$key' => '$expectedMessage'".PHP_EOL.PHP_EOL.$errorMessage
);
}
}
}

return $this;
}








public function assertJsonValidationErrorFor($key, $responseKey = 'errors')
{
$jsonErrors = Arr::get($this->json(), $responseKey) ?? [];

$errorMessage = $jsonErrors
? 'Response has the following JSON validation errors:'.
PHP_EOL.PHP_EOL.json_encode($jsonErrors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL
: 'Response does not have JSON validation errors.';

PHPUnit::withResponse($this)->assertArrayHasKey(
$key,
$jsonErrors,
"Failed to find a validation error in the response for key: '{$key}'".PHP_EOL.PHP_EOL.$errorMessage
);

return $this;
}








public function assertJsonMissingValidationErrors($keys = null, $responseKey = 'errors')
{
if ($this->getContent() === '') {
PHPUnit::withResponse($this)->assertTrue(true);

return $this;
}

$json = $this->json();

if (! Arr::has($json, $responseKey)) {
PHPUnit::withResponse($this)->assertTrue(true);

return $this;
}

$errors = Arr::get($json, $responseKey, []);

if (is_null($keys) && count($errors) > 0) {
PHPUnit::withResponse($this)->fail(
'Response has unexpected validation errors: '.PHP_EOL.PHP_EOL.
json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
);
}

foreach (Arr::wrap($keys) as $key) {
PHPUnit::withResponse($this)->assertFalse(
isset($errors[$key]),
"Found unexpected validation error for key: '{$key}'"
);
}

return $this;
}







public function assertJsonIsArray($key = null)
{
$data = $this->json($key);

$encodedData = json_encode($data);

PHPUnit::withResponse($this)->assertTrue(
is_array($data)
&& str_starts_with($encodedData, '[')
&& str_ends_with($encodedData, ']')
);

return $this;
}







public function assertJsonIsObject($key = null)
{
$data = $this->json($key);

$encodedData = json_encode($data);

PHPUnit::withResponse($this)->assertTrue(
is_array($data)
&& str_starts_with($encodedData, '{')
&& str_ends_with($encodedData, '}')
);

return $this;
}








public function decodeResponseJson()
{
$testJson = new AssertableJsonString($this->getContent());

$decodedResponse = $testJson->json();

if (is_null($decodedResponse) || $decodedResponse === false) {
if ($this->exception) {
throw $this->exception;
} else {
PHPUnit::withResponse($this)->fail('Invalid JSON was returned from the route.');
}
}

return $testJson;
}







public function json($key = null)
{
return $this->decodeResponseJson()->json($key);
}







public function collect($key = null)
{
return Collection::make($this->json($key));
}







public function assertViewIs($value)
{
$this->ensureResponseHasView();

PHPUnit::withResponse($this)->assertEquals($value, $this->original->name());

return $this;
}








public function assertViewHas($key, $value = null)
{
if (is_array($key)) {
return $this->assertViewHasAll($key);
}

$this->ensureResponseHasView();

if (is_null($value)) {
PHPUnit::withResponse($this)->assertTrue(Arr::has($this->original->gatherData(), $key));
} elseif ($value instanceof Closure) {
PHPUnit::withResponse($this)->assertTrue($value(Arr::get($this->original->gatherData(), $key)));
} elseif ($value instanceof Model) {
PHPUnit::withResponse($this)->assertTrue($value->is(Arr::get($this->original->gatherData(), $key)));
} elseif ($value instanceof EloquentCollection) {
$actual = Arr::get($this->original->gatherData(), $key);

PHPUnit::withResponse($this)->assertInstanceOf(EloquentCollection::class, $actual);
PHPUnit::withResponse($this)->assertSameSize($value, $actual);

$value->each(fn ($item, $index) => PHPUnit::withResponse($this)->assertTrue($actual->get($index)->is($item)));
} else {
PHPUnit::withResponse($this)->assertEquals($value, Arr::get($this->original->gatherData(), $key));
}

return $this;
}







public function assertViewHasAll(array $bindings)
{
foreach ($bindings as $key => $value) {
if (is_int($key)) {
$this->assertViewHas($value);
} else {
$this->assertViewHas($key, $value);
}
}

return $this;
}







public function viewData($key)
{
$this->ensureResponseHasView();

return $this->original->gatherData()[$key];
}







public function assertViewMissing($key)
{
$this->ensureResponseHasView();

PHPUnit::withResponse($this)->assertFalse(Arr::has($this->original->gatherData(), $key));

return $this;
}






protected function ensureResponseHasView()
{
if (! $this->responseHasView()) {
return PHPUnit::withResponse($this)->fail('The response is not a view.');
}

return $this;
}






protected function responseHasView()
{
return isset($this->original) && $this->original instanceof View;
}









public function assertValid($keys = null, $errorBag = 'default', $responseKey = 'errors')
{
if ($this->baseResponse->headers->get('Content-Type') === 'application/json') {
return $this->assertJsonMissingValidationErrors($keys, $responseKey);
}

if ($this->session()->get('errors')) {
$errors = $this->session()->get('errors')->getBag($errorBag)->getMessages();
} else {
$errors = [];
}

if (empty($errors)) {
PHPUnit::withResponse($this)->assertTrue(true);

return $this;
}

if (is_null($keys) && count($errors) > 0) {
PHPUnit::withResponse($this)->fail(
'Response has unexpected validation errors: '.PHP_EOL.PHP_EOL.
json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
);
}

foreach (Arr::wrap($keys) as $key) {
PHPUnit::withResponse($this)->assertFalse(
isset($errors[$key]),
"Found unexpected validation error for key: '{$key}'"
);
}

return $this;
}









public function assertInvalid($errors = null,
$errorBag = 'default',
$responseKey = 'errors')
{
if ($this->baseResponse->headers->get('Content-Type') === 'application/json') {
return $this->assertJsonValidationErrors($errors, $responseKey);
}

$this->assertSessionHas('errors');

$sessionErrors = $this->session()->get('errors')->getBag($errorBag)->getMessages();

$errorMessage = $sessionErrors
? 'Response has the following validation errors in the session:'.
PHP_EOL.PHP_EOL.json_encode($sessionErrors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL
: 'Response does not have validation errors in the session.';

foreach (Arr::wrap($errors) as $key => $value) {
PHPUnit::withResponse($this)->assertArrayHasKey(
$resolvedKey = (is_int($key)) ? $value : $key,
$sessionErrors,
"Failed to find a validation error in session for key: '{$resolvedKey}'".PHP_EOL.PHP_EOL.$errorMessage
);

foreach (Arr::wrap($value) as $message) {
if (! is_int($key)) {
$hasError = false;

foreach (Arr::wrap($sessionErrors[$key]) as $sessionErrorMessage) {
if (Str::contains($sessionErrorMessage, $message)) {
$hasError = true;

break;
}
}

if (! $hasError) {
PHPUnit::withResponse($this)->fail(
"Failed to find a validation error for key and message: '$key' => '$message'".PHP_EOL.PHP_EOL.$errorMessage
);
}
}
}
}

return $this;
}








public function assertSessionHas($key, $value = null)
{
if (is_array($key)) {
return $this->assertSessionHasAll($key);
}

if (is_null($value)) {
PHPUnit::withResponse($this)->assertTrue(
$this->session()->has($key),
"Session is missing expected key [{$key}]."
);
} elseif ($value instanceof Closure) {
PHPUnit::withResponse($this)->assertTrue($value($this->session()->get($key)));
} else {
PHPUnit::withResponse($this)->assertEquals($value, $this->session()->get($key));
}

return $this;
}







public function assertSessionHasAll(array $bindings)
{
foreach ($bindings as $key => $value) {
if (is_int($key)) {
$this->assertSessionHas($value);
} else {
$this->assertSessionHas($key, $value);
}
}

return $this;
}








public function assertSessionHasInput($key, $value = null)
{
if (is_array($key)) {
foreach ($key as $k => $v) {
if (is_int($k)) {
$this->assertSessionHasInput($v);
} else {
$this->assertSessionHasInput($k, $v);
}
}

return $this;
}

if (is_null($value)) {
PHPUnit::withResponse($this)->assertTrue(
$this->session()->hasOldInput($key),
"Session is missing expected key [{$key}]."
);
} elseif ($value instanceof Closure) {
PHPUnit::withResponse($this)->assertTrue($value($this->session()->getOldInput($key)));
} else {
PHPUnit::withResponse($this)->assertEquals($value, $this->session()->getOldInput($key));
}

return $this;
}









public function assertSessionHasErrors($keys = [], $format = null, $errorBag = 'default')
{
$this->assertSessionHas('errors');

$keys = (array) $keys;

$errors = $this->session()->get('errors')->getBag($errorBag);

foreach ($keys as $key => $value) {
if (is_int($key)) {
PHPUnit::withResponse($this)->assertTrue($errors->has($value), "Session missing error: $value");
} else {
PHPUnit::withResponse($this)->assertContains(is_bool($value) ? (string) $value : $value, $errors->get($key, $format));
}
}

return $this;
}









public function assertSessionDoesntHaveErrors($keys = [], $format = null, $errorBag = 'default')
{
$keys = (array) $keys;

if (empty($keys)) {
return $this->assertSessionHasNoErrors();
}

if (is_null($this->session()->get('errors'))) {
PHPUnit::withResponse($this)->assertTrue(true);

return $this;
}

$errors = $this->session()->get('errors')->getBag($errorBag);

foreach ($keys as $key => $value) {
if (is_int($key)) {
PHPUnit::withResponse($this)->assertFalse($errors->has($value), "Session has unexpected error: $value");
} else {
PHPUnit::withResponse($this)->assertNotContains($value, $errors->get($key, $format));
}
}

return $this;
}






public function assertSessionHasNoErrors()
{
$hasErrors = $this->session()->has('errors');

PHPUnit::withResponse($this)->assertFalse(
$hasErrors,
'Session has unexpected errors: '.PHP_EOL.PHP_EOL.
json_encode((function () use ($hasErrors) {
$errors = [];

$sessionErrors = $this->session()->get('errors');

if ($hasErrors && is_a($sessionErrors, ViewErrorBag::class)) {
foreach ($sessionErrors->getBags() as $bag => $messages) {
if (is_a($messages, MessageBag::class)) {
$errors[$bag] = $messages->all();
}
}
}

return $errors;
})(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
);

return $this;
}









public function assertSessionHasErrorsIn($errorBag, $keys = [], $format = null)
{
return $this->assertSessionHasErrors($keys, $format, $errorBag);
}







public function assertSessionMissing($key)
{
if (is_array($key)) {
foreach ($key as $value) {
$this->assertSessionMissing($value);
}
} else {
PHPUnit::withResponse($this)->assertFalse(
$this->session()->has($key),
"Session has unexpected key [{$key}]."
);
}

return $this;
}






protected function session()
{
$session = app('session.store');

if (! $session->isStarted()) {
$session->start();
}

return $session;
}






public function ddHeaders()
{
$this->dumpHeaders();

exit(1);
}







public function ddSession($keys = [])
{
$this->dumpSession($keys);

exit(1);
}







public function dump($key = null)
{
$content = $this->getContent();

$json = json_decode($content);

if (json_last_error() === JSON_ERROR_NONE) {
$content = $json;
}

if (! is_null($key)) {
dump(data_get($content, $key));
} else {
dump($content);
}

return $this;
}






public function dumpHeaders()
{
dump($this->headers->all());

return $this;
}







public function dumpSession($keys = [])
{
$keys = (array) $keys;

if (empty($keys)) {
dump($this->session()->all());
} else {
dump($this->session()->only($keys));
}

return $this;
}






public function streamedContent()
{
if (! is_null($this->streamedContent)) {
return $this->streamedContent;
}

if (! $this->baseResponse instanceof StreamedResponse
&& ! $this->baseResponse instanceof StreamedJsonResponse) {
PHPUnit::withResponse($this)->fail('The response is not a streamed response.');
}

ob_start(function (string $buffer): string {
$this->streamedContent .= $buffer;

return '';
});

$this->sendContent();

ob_end_clean();

return $this->streamedContent;
}







public function withExceptions(Collection $exceptions)
{
$this->exceptions = $exceptions;

return $this;
}







public function __get($key)
{
return $this->baseResponse->{$key};
}







public function __isset($key)
{
return isset($this->baseResponse->{$key});
}







public function offsetExists($offset): bool
{
return $this->responseHasView()
? isset($this->original->gatherData()[$offset])
: isset($this->json()[$offset]);
}







public function offsetGet($offset): mixed
{
return $this->responseHasView()
? $this->viewData($offset)
: $this->json()[$offset];
}










public function offsetSet($offset, $value): void
{
throw new LogicException('Response data may not be mutated using array access.');
}









public function offsetUnset($offset): void
{
throw new LogicException('Response data may not be mutated using array access.');
}








public function __call($method, $args)
{
if (static::hasMacro($method)) {
return $this->macroCall($method, $args);
}

return $this->baseResponse->{$method}(...$args);
}
}
