<?php

namespace Illuminate\Database\Events;

class ModelPruningFinished
{





public $models;







public function __construct($models)
{
$this->models = $models;
}
}
