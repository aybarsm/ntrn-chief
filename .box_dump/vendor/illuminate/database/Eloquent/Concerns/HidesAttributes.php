<?php

namespace Illuminate\Database\Eloquent\Concerns;

trait HidesAttributes
{





protected $hidden = [];






protected $visible = [];






public function getHidden()
{
return $this->hidden;
}







public function setHidden(array $hidden)
{
$this->hidden = $hidden;

return $this;
}






public function getVisible()
{
return $this->visible;
}







public function setVisible(array $visible)
{
$this->visible = $visible;

return $this;
}







public function makeVisible($attributes)
{
$attributes = is_array($attributes) ? $attributes : func_get_args();

$this->hidden = array_diff($this->hidden, $attributes);

if (! empty($this->visible)) {
$this->visible = array_values(array_unique(array_merge($this->visible, $attributes)));
}

return $this;
}








public function makeVisibleIf($condition, $attributes)
{
return value($condition, $this) ? $this->makeVisible($attributes) : $this;
}







public function makeHidden($attributes)
{
$this->hidden = array_values(array_unique(array_merge(
$this->hidden, is_array($attributes) ? $attributes : func_get_args()
)));

return $this;
}








public function makeHiddenIf($condition, $attributes)
{
return value($condition, $this) ? $this->makeHidden($attributes) : $this;
}
}
