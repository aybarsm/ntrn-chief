<?php

namespace Illuminate\Database\Query;

class IndexHint
{





public $type;






public $index;








public function __construct($type, $index)
{
$this->type = $type;
$this->index = $index;
}
}
