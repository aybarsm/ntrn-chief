<?php

namespace Faker;

/**
@mixin





*/
class DefaultGenerator
{
protected $default;

public function __construct($default = null)
{
trigger_deprecation('fakerphp/faker', '1.16', 'Class "%s" is deprecated, use "%s" instead.', __CLASS__, ChanceGenerator::class);

$this->default = $default;
}

public function ext()
{
return $this;
}






public function __get($attribute)
{
trigger_deprecation('fakerphp/faker', '1.14', 'Accessing property "%s" is deprecated, use "%s()" instead.', $attribute, $attribute);

return $this->default;
}





public function __call($method, $attributes)
{
return $this->default;
}
}
