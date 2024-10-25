<?php

namespace Illuminate\Database\Eloquent\Casts;

class Attribute
{





public $get;






public $set;






public $withCaching = false;






public $withObjectCaching = true;








public function __construct(?callable $get = null, ?callable $set = null)
{
$this->get = $get;
$this->set = $set;
}








public static function make(?callable $get = null, ?callable $set = null): static
{
return new static($get, $set);
}







public static function get(callable $get)
{
return new static($get);
}







public static function set(callable $set)
{
return new static(null, $set);
}






public function withoutObjectCaching()
{
$this->withObjectCaching = false;

return $this;
}






public function shouldCache()
{
$this->withCaching = true;

return $this;
}
}
