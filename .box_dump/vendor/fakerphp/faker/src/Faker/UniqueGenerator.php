<?php

namespace Faker;

use Faker\Extension\Extension;

/**
@mixin




*/
class UniqueGenerator
{
protected $generator;
protected $maxRetries;











protected $uniques = [];






public function __construct($generator, $maxRetries = 10000, &$uniques = [])
{
$this->generator = $generator;
$this->maxRetries = $maxRetries;
$this->uniques = &$uniques;
}

public function ext(string $id)
{
return new self($this->generator->ext($id), $this->maxRetries, $this->uniques);
}








public function __get($attribute)
{
trigger_deprecation('fakerphp/faker', '1.14', 'Accessing property "%s" is deprecated, use "%s()" instead.', $attribute, $attribute);

return $this->__call($attribute, []);
}







public function __call($name, $arguments)
{
if (!isset($this->uniques[$name])) {
$this->uniques[$name] = [];
}
$i = 0;

do {
$res = call_user_func_array([$this->generator, $name], $arguments);
++$i;

if ($i > $this->maxRetries) {
throw new \OverflowException(sprintf('Maximum retries of %d reached without finding a unique value', $this->maxRetries));
}
} while (array_key_exists(serialize($res), $this->uniques[$name]));
$this->uniques[$name][serialize($res)] = null;

return $res;
}
}
