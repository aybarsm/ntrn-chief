<?php

namespace Illuminate\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\MessageBag as MessageBagContract;
use Illuminate\Contracts\Support\MessageProvider;
use JsonSerializable;
use Stringable;

class MessageBag implements Jsonable, JsonSerializable, MessageBagContract, MessageProvider, Stringable
{





protected $messages = [];






protected $format = ':message';







public function __construct(array $messages = [])
{
foreach ($messages as $key => $value) {
$value = $value instanceof Arrayable ? $value->toArray() : (array) $value;

$this->messages[$key] = array_unique($value);
}
}






public function keys()
{
return array_keys($this->messages);
}








public function add($key, $message)
{
if ($this->isUnique($key, $message)) {
$this->messages[$key][] = $message;
}

return $this;
}









public function addIf($boolean, $key, $message)
{
return $boolean ? $this->add($key, $message) : $this;
}








protected function isUnique($key, $message)
{
$messages = (array) $this->messages;

return ! isset($messages[$key]) || ! in_array($message, $messages[$key]);
}







public function merge($messages)
{
if ($messages instanceof MessageProvider) {
$messages = $messages->getMessageBag()->getMessages();
}

$this->messages = array_merge_recursive($this->messages, $messages);

return $this;
}







public function has($key)
{
if ($this->isEmpty()) {
return false;
}

if (is_null($key)) {
return $this->any();
}

$keys = is_array($key) ? $key : func_get_args();

foreach ($keys as $key) {
if ($this->first($key) === '') {
return false;
}
}

return true;
}







public function hasAny($keys = [])
{
if ($this->isEmpty()) {
return false;
}

$keys = is_array($keys) ? $keys : func_get_args();

foreach ($keys as $key) {
if ($this->has($key)) {
return true;
}
}

return false;
}







public function missing($key)
{
$keys = is_array($key) ? $key : func_get_args();

return ! $this->hasAny($keys);
}








public function first($key = null, $format = null)
{
$messages = is_null($key) ? $this->all($format) : $this->get($key, $format);

$firstMessage = Arr::first($messages, null, '');

return is_array($firstMessage) ? Arr::first($firstMessage) : $firstMessage;
}








public function get($key, $format = null)
{



if (array_key_exists($key, $this->messages)) {
return $this->transform(
$this->messages[$key], $this->checkFormat($format), $key
);
}

if (str_contains($key, '*')) {
return $this->getMessagesForWildcardKey($key, $format);
}

return [];
}








protected function getMessagesForWildcardKey($key, $format)
{
return collect($this->messages)
->filter(function ($messages, $messageKey) use ($key) {
return Str::is($key, $messageKey);
})
->map(function ($messages, $messageKey) use ($format) {
return $this->transform(
$messages, $this->checkFormat($format), $messageKey
);
})->all();
}







public function all($format = null)
{
$format = $this->checkFormat($format);

$all = [];

foreach ($this->messages as $key => $messages) {
$all = array_merge($all, $this->transform($messages, $format, $key));
}

return $all;
}







public function unique($format = null)
{
return array_unique($this->all($format));
}







public function forget($key)
{
unset($this->messages[$key]);

return $this;
}









protected function transform($messages, $format, $messageKey)
{
if ($format == ':message') {
return (array) $messages;
}

return collect((array) $messages)
->map(function ($message) use ($format, $messageKey) {



return str_replace([':message', ':key'], [$message, $messageKey], $format);
})->all();
}







protected function checkFormat($format)
{
return $format ?: $this->format;
}






public function messages()
{
return $this->messages;
}






public function getMessages()
{
return $this->messages();
}






public function getMessageBag()
{
return $this;
}






public function getFormat()
{
return $this->format;
}







public function setFormat($format = ':message')
{
$this->format = $format;

return $this;
}






public function isEmpty()
{
return ! $this->any();
}






public function isNotEmpty()
{
return $this->any();
}






public function any()
{
return $this->count() > 0;
}






public function count(): int
{
return count($this->messages, COUNT_RECURSIVE) - count($this->messages);
}






public function toArray()
{
return $this->getMessages();
}






public function jsonSerialize(): array
{
return $this->toArray();
}







public function toJson($options = 0)
{
return json_encode($this->jsonSerialize(), $options);
}






public function __toString()
{
return $this->toJson();
}
}
