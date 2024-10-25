<?php

namespace Illuminate\Log\Context\Events;

class ContextDehydrating
{





public $context;






public function __construct($context)
{
$this->context = $context;
}
}
