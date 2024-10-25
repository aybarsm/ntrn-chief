<?php

namespace Illuminate\Database\Eloquent\Casts;

use ArrayObject as BaseArrayObject;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
@template
@template
@extends

*/
class ArrayObject extends BaseArrayObject implements Arrayable, JsonSerializable
{





public function collect()
{
return collect($this->getArrayCopy());
}






public function toArray()
{
return $this->getArrayCopy();
}






public function jsonSerialize(): array
{
return $this->getArrayCopy();
}
}
