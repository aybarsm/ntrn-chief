<?php

namespace Illuminate\Http\Resources;

use Illuminate\Support\Collection;
use JsonSerializable;

class MergeValue
{





public $data;







public function __construct($data)
{
if ($data instanceof Collection) {
$this->data = $data->all();
} elseif ($data instanceof JsonSerializable) {
$this->data = $data->jsonSerialize();
} else {
$this->data = $data;
}
}
}
