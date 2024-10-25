<?php

namespace Illuminate\Session;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
use SessionHandlerInterface;

class EncryptedStore extends Store
{





protected $encrypter;











public function __construct($name, SessionHandlerInterface $handler, EncrypterContract $encrypter, $id = null, $serialization = 'php')
{
$this->encrypter = $encrypter;

parent::__construct($name, $handler, $id, $serialization);
}







protected function prepareForUnserialize($data)
{
try {
return $this->encrypter->decrypt($data);
} catch (DecryptException) {
return $this->serialization === 'json' ? json_encode([]) : serialize([]);
}
}







protected function prepareForStorage($data)
{
return $this->encrypter->encrypt($data);
}






public function getEncrypter()
{
return $this->encrypter;
}
}
