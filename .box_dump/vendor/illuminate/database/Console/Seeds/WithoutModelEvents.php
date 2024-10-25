<?php

namespace Illuminate\Database\Console\Seeds;

use Illuminate\Database\Eloquent\Model;

trait WithoutModelEvents
{






public function withoutModelEvents(callable $callback)
{
return fn () => Model::withoutEvents($callback);
}
}
