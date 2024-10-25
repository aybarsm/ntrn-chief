<?php

namespace Illuminate\Bus\Events;

use Illuminate\Bus\Batch;

class BatchDispatched
{





public $batch;







public function __construct(Batch $batch)
{
$this->batch = $batch;
}
}
