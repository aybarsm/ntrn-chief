<?php

namespace Illuminate\Encryption;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;

class EncryptionServiceProvider extends ServiceProvider
{





public function register()
{
$this->registerEncrypter();
$this->registerSerializableClosureSecurityKey();
}






protected function registerEncrypter()
{
$this->app->singleton('encrypter', function ($app) {
$config = $app->make('config')->get('app');

return (new Encrypter($this->parseKey($config), $config['cipher']))
->previousKeys(array_map(
fn ($key) => $this->parseKey(['key' => $key]),
$config['previous_keys'] ?? []
));
});
}






protected function registerSerializableClosureSecurityKey()
{
$config = $this->app->make('config')->get('app');

if (! class_exists(SerializableClosure::class) || empty($config['key'])) {
return;
}

SerializableClosure::setSecretKey($this->parseKey($config));
}







protected function parseKey(array $config)
{
if (Str::startsWith($key = $this->key($config), $prefix = 'base64:')) {
$key = base64_decode(Str::after($key, $prefix));
}

return $key;
}









protected function key(array $config)
{
return tap($config['key'], function ($key) {
if (empty($key)) {
throw new MissingAppKeyException;
}
});
}
}
