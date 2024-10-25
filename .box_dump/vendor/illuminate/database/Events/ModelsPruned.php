<?php

namespace Illuminate\Database\Events;

class ModelsPruned
{





public $model;






public $count;








public function __construct($model, $count)
{
$this->model = $model;
$this->count = $count;
}
}
