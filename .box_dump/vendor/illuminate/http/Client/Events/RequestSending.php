<?php

namespace Illuminate\Http\Client\Events;

use Illuminate\Http\Client\Request;

class RequestSending
{





public $request;







public function __construct(Request $request)
{
$this->request = $request;
}
}
