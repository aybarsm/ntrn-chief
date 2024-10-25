<?php

namespace Faker;

use Faker\Extension\Extension;

/**
@mixin



*/
class ChanceGenerator
{
private $generator;
private $weight;
protected $default;




public function __construct($generator, float $weight, $default = null)
{
$this->default = $default;
$this->generator = $generator;
$this->weight = $weight;
}

public function ext(string $id)
{
return new self($this->generator->ext($id), $this->weight, $this->default);
}








public function __get($attribute)
{
trigger_deprecation('fakerphp/faker', '1.14', 'Accessing property "%s" is deprecated, use "%s()" instead.', $attribute, $attribute);

return $this->__call($attribute, []);
}





public function __call($name, $arguments)
{
if (mt_rand(1, 100) <= (100 * $this->weight)) {
return call_user_func_array([$this->generator, $name], $arguments);
}

return $this->default;
}
}
