<?php

namespace Faker;

use Faker\Extension\Extension;

/**
@mixin



*/
class ValidGenerator
{
protected $generator;
protected $validator;
protected $maxRetries;






public function __construct($generator, $validator = null, $maxRetries = 10000)
{
if (null === $validator) {
$validator = static function () {
return true;
};
} elseif (!is_callable($validator)) {
throw new \InvalidArgumentException('valid() only accepts callables as first argument');
}
$this->generator = $generator;
$this->validator = $validator;
$this->maxRetries = $maxRetries;
}

public function ext(string $id)
{
return new self($this->generator->ext($id), $this->validator, $this->maxRetries);
}








public function __get($attribute)
{
trigger_deprecation('fakerphp/faker', '1.14', 'Accessing property "%s" is deprecated, use "%s()" instead.', $attribute, $attribute);

return $this->__call($attribute, []);
}







public function __call($name, $arguments)
{
$i = 0;

do {
$res = call_user_func_array([$this->generator, $name], $arguments);
++$i;

if ($i > $this->maxRetries) {
throw new \OverflowException(sprintf('Maximum retries of %d reached without finding a valid value', $this->maxRetries));
}
} while (!call_user_func($this->validator, $res));

return $res;
}
}
