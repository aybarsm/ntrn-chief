<?php

namespace Illuminate\Database\Eloquent\Concerns;

trait HasUniqueIds
{





public $usesUniqueIds = false;






public function usesUniqueIds()
{
return $this->usesUniqueIds;
}






public function setUniqueIds()
{
foreach ($this->uniqueIds() as $column) {
if (empty($this->{$column})) {
$this->{$column} = $this->newUniqueId();
}
}
}






public function newUniqueId()
{
return null;
}






public function uniqueIds()
{
return [];
}
}
