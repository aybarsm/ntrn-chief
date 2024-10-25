<?php

namespace Illuminate\Log\Context\Events;

class ContextHydrated
{





public $context;






public function __construct($context)
{
$this->context = $context;
}
}
