<?php

namespace Illuminate\Database\Schema;

use Illuminate\Support\Fluent;









class ForeignKeyDefinition extends Fluent
{





public function cascadeOnUpdate()
{
return $this->onUpdate('cascade');
}






public function restrictOnUpdate()
{
return $this->onUpdate('restrict');
}






public function nullOnUpdate()
{
return $this->onUpdate('set null');
}






public function noActionOnUpdate()
{
return $this->onUpdate('no action');
}






public function cascadeOnDelete()
{
return $this->onDelete('cascade');
}






public function restrictOnDelete()
{
return $this->onDelete('restrict');
}






public function nullOnDelete()
{
return $this->onDelete('set null');
}






public function noActionOnDelete()
{
return $this->onDelete('no action');
}
}
