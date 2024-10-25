<?php

namespace Illuminate\Database\Eloquent;

use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Arr;

/**
@template
*/
class ModelNotFoundException extends RecordsNotFoundException
{





protected $model;






protected $ids;








public function setModel($model, $ids = [])
{
$this->model = $model;
$this->ids = Arr::wrap($ids);

$this->message = "No query results for model [{$model}]";

if (count($this->ids) > 0) {
$this->message .= ' '.implode(', ', $this->ids);
} else {
$this->message .= '.';
}

return $this;
}






public function getModel()
{
return $this->model;
}






public function getIds()
{
return $this->ids;
}
}
