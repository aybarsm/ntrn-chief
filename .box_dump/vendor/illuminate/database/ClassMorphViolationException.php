<?php

namespace Illuminate\Database;

use RuntimeException;

class ClassMorphViolationException extends RuntimeException
{





public $model;






public function __construct($model)
{
$class = get_class($model);

parent::__construct("No morph map defined for model [{$class}].");

$this->model = $class;
}
}
