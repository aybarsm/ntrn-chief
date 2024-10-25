<?php

namespace Illuminate\Session;

use Closure;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\ViewErrorBag;
use SessionHandlerInterface;
use stdClass;

class Store implements Session
{
use Macroable;






protected $id;






protected $name;






protected $attributes = [];






protected $handler;






protected $serialization = 'php';






protected $started = false;










public function __construct($name, SessionHandlerInterface $handler, $id = null, $serialization = 'php')
{
$this->setId($id);
$this->name = $name;
$this->handler = $handler;
$this->serialization = $serialization;
}






public function start()
{
$this->loadSession();

if (! $this->has('_token')) {
$this->regenerateToken();
}

return $this->started = true;
}






protected function loadSession()
{
$this->attributes = array_replace($this->attributes, $this->readFromHandler());

$this->marshalErrorBag();
}






protected function readFromHandler()
{
if ($data = $this->handler->read($this->getId())) {
if ($this->serialization === 'json') {
$data = json_decode($this->prepareForUnserialize($data), true);
} else {
$data = @unserialize($this->prepareForUnserialize($data));
}

if ($data !== false && is_array($data)) {
return $data;
}
}

return [];
}







protected function prepareForUnserialize($data)
{
return $data;
}






protected function marshalErrorBag()
{
if ($this->serialization !== 'json' || $this->missing('errors')) {
return;
}

$errorBag = new ViewErrorBag;

foreach ($this->get('errors') as $key => $value) {
$messageBag = new MessageBag($value['messages']);

$errorBag->put($key, $messageBag->setFormat($value['format']));
}

$this->put('errors', $errorBag);
}






public function save()
{
$this->ageFlashData();

$this->prepareErrorBagForSerialization();

$this->handler->write($this->getId(), $this->prepareForStorage(
$this->serialization === 'json' ? json_encode($this->attributes) : serialize($this->attributes)
));

$this->started = false;
}






protected function prepareErrorBagForSerialization()
{
if ($this->serialization !== 'json' || $this->missing('errors')) {
return;
}

$errors = [];

foreach ($this->attributes['errors']->getBags() as $key => $value) {
$errors[$key] = [
'format' => $value->getFormat(),
'messages' => $value->getMessages(),
];
}

$this->attributes['errors'] = $errors;
}







protected function prepareForStorage($data)
{
return $data;
}






public function ageFlashData()
{
$this->forget($this->get('_flash.old', []));

$this->put('_flash.old', $this->get('_flash.new', []));

$this->put('_flash.new', []);
}






public function all()
{
return $this->attributes;
}







public function only(array $keys)
{
return Arr::only($this->attributes, $keys);
}







public function except(array $keys)
{
return Arr::except($this->attributes, $keys);
}







public function exists($key)
{
$placeholder = new stdClass;

return ! collect(is_array($key) ? $key : func_get_args())->contains(function ($key) use ($placeholder) {
return $this->get($key, $placeholder) === $placeholder;
});
}







public function missing($key)
{
return ! $this->exists($key);
}







public function has($key)
{
return ! collect(is_array($key) ? $key : func_get_args())->contains(function ($key) {
return is_null($this->get($key));
});
}







public function hasAny($key)
{
return collect(is_array($key) ? $key : func_get_args())->filter(function ($key) {
return ! is_null($this->get($key));
})->count() >= 1;
}








public function get($key, $default = null)
{
return Arr::get($this->attributes, $key, $default);
}








public function pull($key, $default = null)
{
return Arr::pull($this->attributes, $key, $default);
}







public function hasOldInput($key = null)
{
$old = $this->getOldInput($key);

return is_null($key) ? count($old) > 0 : ! is_null($old);
}








public function getOldInput($key = null, $default = null)
{
return Arr::get($this->get('_old_input', []), $key, $default);
}







public function replace(array $attributes)
{
$this->put($attributes);
}








public function put($key, $value = null)
{
if (! is_array($key)) {
$key = [$key => $value];
}

foreach ($key as $arrayKey => $arrayValue) {
Arr::set($this->attributes, $arrayKey, $arrayValue);
}
}








public function remember($key, Closure $callback)
{
if (! is_null($value = $this->get($key))) {
return $value;
}

return tap($callback(), function ($value) use ($key) {
$this->put($key, $value);
});
}








public function push($key, $value)
{
$array = $this->get($key, []);

$array[] = $value;

$this->put($key, $array);
}








public function increment($key, $amount = 1)
{
$this->put($key, $value = $this->get($key, 0) + $amount);

return $value;
}








public function decrement($key, $amount = 1)
{
return $this->increment($key, $amount * -1);
}








public function flash(string $key, $value = true)
{
$this->put($key, $value);

$this->push('_flash.new', $key);

$this->removeFromOldFlashData([$key]);
}








public function now($key, $value)
{
$this->put($key, $value);

$this->push('_flash.old', $key);
}






public function reflash()
{
$this->mergeNewFlashes($this->get('_flash.old', []));

$this->put('_flash.old', []);
}







public function keep($keys = null)
{
$this->mergeNewFlashes($keys = is_array($keys) ? $keys : func_get_args());

$this->removeFromOldFlashData($keys);
}







protected function mergeNewFlashes(array $keys)
{
$values = array_unique(array_merge($this->get('_flash.new', []), $keys));

$this->put('_flash.new', $values);
}







protected function removeFromOldFlashData(array $keys)
{
$this->put('_flash.old', array_diff($this->get('_flash.old', []), $keys));
}







public function flashInput(array $value)
{
$this->flash('_old_input', $value);
}







public function remove($key)
{
return Arr::pull($this->attributes, $key);
}







public function forget($keys)
{
Arr::forget($this->attributes, $keys);
}






public function flush()
{
$this->attributes = [];
}






public function invalidate()
{
$this->flush();

return $this->migrate(true);
}







public function regenerate($destroy = false)
{
return tap($this->migrate($destroy), function () {
$this->regenerateToken();
});
}







public function migrate($destroy = false)
{
if ($destroy) {
$this->handler->destroy($this->getId());
}

$this->setExists(false);

$this->setId($this->generateSessionId());

return true;
}






public function isStarted()
{
return $this->started;
}






public function getName()
{
return $this->name;
}







public function setName($name)
{
$this->name = $name;
}






public function id()
{
return $this->getId();
}






public function getId()
{
return $this->id;
}







public function setId($id)
{
$this->id = $this->isValidId($id) ? $id : $this->generateSessionId();
}







public function isValidId($id)
{
return is_string($id) && ctype_alnum($id) && strlen($id) === 40;
}






protected function generateSessionId()
{
return Str::random(40);
}







public function setExists($value)
{
if ($this->handler instanceof ExistenceAwareInterface) {
$this->handler->setExists($value);
}
}






public function token()
{
return $this->get('_token');
}






public function regenerateToken()
{
$this->put('_token', Str::random(40));
}






public function previousUrl()
{
return $this->get('_previous.url');
}







public function setPreviousUrl($url)
{
$this->put('_previous.url', $url);
}






public function passwordConfirmed()
{
$this->put('auth.password_confirmed_at', time());
}






public function getHandler()
{
return $this->handler;
}







public function setHandler(SessionHandlerInterface $handler)
{
return $this->handler = $handler;
}






public function handlerNeedsRequest()
{
return $this->handler instanceof CookieSessionHandler;
}







public function setRequestOnHandler($request)
{
if ($this->handlerNeedsRequest()) {
$this->handler->setRequest($request);
}
}
}
