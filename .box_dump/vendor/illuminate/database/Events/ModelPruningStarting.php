<?php

namespace Illuminate\Database\Events;

class ModelPruningStarting
{





public $models;







public function __construct($models)
{
$this->models = $models;
}
}
