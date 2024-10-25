<?php

namespace Illuminate\Encryption;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Contracts\Encryption\StringEncrypter;
use RuntimeException;

class Encrypter implements EncrypterContract, StringEncrypter
{





protected $key;






protected $previousKeys = [];






protected $cipher;






private static $supportedCiphers = [
'aes-128-cbc' => ['size' => 16, 'aead' => false],
'aes-256-cbc' => ['size' => 32, 'aead' => false],
'aes-128-gcm' => ['size' => 16, 'aead' => true],
'aes-256-gcm' => ['size' => 32, 'aead' => true],
];










public function __construct($key, $cipher = 'aes-128-cbc')
{
$key = (string) $key;

if (! static::supported($key, $cipher)) {
$ciphers = implode(', ', array_keys(self::$supportedCiphers));

throw new RuntimeException("Unsupported cipher or incorrect key length. Supported ciphers are: {$ciphers}.");
}

$this->key = $key;
$this->cipher = $cipher;
}








public static function supported($key, $cipher)
{
if (! isset(self::$supportedCiphers[strtolower($cipher)])) {
return false;
}

return mb_strlen($key, '8bit') === self::$supportedCiphers[strtolower($cipher)]['size'];
}







public static function generateKey($cipher)
{
return random_bytes(self::$supportedCiphers[strtolower($cipher)]['size'] ?? 32);
}










public function encrypt(#[\SensitiveParameter] $value, $serialize = true)
{
$iv = random_bytes(openssl_cipher_iv_length(strtolower($this->cipher)));

$value = \openssl_encrypt(
$serialize ? serialize($value) : $value,
strtolower($this->cipher), $this->key, 0, $iv, $tag
);

if ($value === false) {
throw new EncryptException('Could not encrypt the data.');
}

$iv = base64_encode($iv);
$tag = base64_encode($tag ?? '');

$mac = self::$supportedCiphers[strtolower($this->cipher)]['aead']
? '' 
: $this->hash($iv, $value, $this->key);

$json = json_encode(compact('iv', 'value', 'mac', 'tag'), JSON_UNESCAPED_SLASHES);

if (json_last_error() !== JSON_ERROR_NONE) {
throw new EncryptException('Could not encrypt the data.');
}

return base64_encode($json);
}









public function encryptString(#[\SensitiveParameter] $value)
{
return $this->encrypt($value, false);
}










public function decrypt($payload, $unserialize = true)
{
$payload = $this->getJsonPayload($payload);

$iv = base64_decode($payload['iv']);

$this->ensureTagIsValid(
$tag = empty($payload['tag']) ? null : base64_decode($payload['tag'])
);

$foundValidMac = false;




foreach ($this->getAllKeys() as $key) {
if (
$this->shouldValidateMac() &&
! ($foundValidMac = $foundValidMac || $this->validMacForKey($payload, $key))
) {
continue;
}

$decrypted = \openssl_decrypt(
$payload['value'], strtolower($this->cipher), $key, 0, $iv, $tag ?? ''
);

if ($decrypted !== false) {
break;
}
}

if ($this->shouldValidateMac() && ! $foundValidMac) {
throw new DecryptException('The MAC is invalid.');
}

if (($decrypted ?? false) === false) {
throw new DecryptException('Could not decrypt the data.');
}

return $unserialize ? unserialize($decrypted) : $decrypted;
}









public function decryptString($payload)
{
return $this->decrypt($payload, false);
}









protected function hash(#[\SensitiveParameter] $iv, #[\SensitiveParameter] $value, #[\SensitiveParameter] $key)
{
return hash_hmac('sha256', $iv.$value, $key);
}









protected function getJsonPayload($payload)
{
if (! is_string($payload)) {
throw new DecryptException('The payload is invalid.');
}

$payload = json_decode(base64_decode($payload), true);




if (! $this->validPayload($payload)) {
throw new DecryptException('The payload is invalid.');
}

return $payload;
}







protected function validPayload($payload)
{
if (! is_array($payload)) {
return false;
}

foreach (['iv', 'value', 'mac'] as $item) {
if (! isset($payload[$item]) || ! is_string($payload[$item])) {
return false;
}
}

if (isset($payload['tag']) && ! is_string($payload['tag'])) {
return false;
}

return strlen(base64_decode($payload['iv'], true)) === openssl_cipher_iv_length(strtolower($this->cipher));
}







protected function validMac(array $payload)
{
return $this->validMacForKey($payload, $this->key);
}








protected function validMacForKey(#[\SensitiveParameter] $payload, $key)
{
return hash_equals(
$this->hash($payload['iv'], $payload['value'], $key), $payload['mac']
);
}







protected function ensureTagIsValid($tag)
{
if (self::$supportedCiphers[strtolower($this->cipher)]['aead'] && strlen($tag) !== 16) {
throw new DecryptException('Could not decrypt the data.');
}

if (! self::$supportedCiphers[strtolower($this->cipher)]['aead'] && is_string($tag)) {
throw new DecryptException('Unable to use tag because the cipher algorithm does not support AEAD.');
}
}






protected function shouldValidateMac()
{
return ! self::$supportedCiphers[strtolower($this->cipher)]['aead'];
}






public function getKey()
{
return $this->key;
}






public function getAllKeys()
{
return [$this->key, ...$this->previousKeys];
}






public function getPreviousKeys()
{
return $this->previousKeys;
}







public function previousKeys(array $keys)
{
foreach ($keys as $key) {
if (! static::supported($key, $this->cipher)) {
$ciphers = implode(', ', array_keys(self::$supportedCiphers));

throw new RuntimeException("Unsupported cipher or incorrect key length. Supported ciphers are: {$ciphers}.");
}
}

$this->previousKeys = $keys;

return $this;
}
}
