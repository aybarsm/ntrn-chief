<?php

namespace Illuminate\Database\Eloquent\Concerns;

use Illuminate\Support\Str;

trait HasVersion7Uuids
{
use HasUuids;






public function newUniqueId()
{
return (string) Str::uuid7();
}
}
