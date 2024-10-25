<?php

namespace Illuminate\Http\Client\Events;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;

class ConnectionFailed
{





public $request;






public $exception;








public function __construct(Request $request, ConnectionException $exception)
{
$this->request = $request;
$this->exception = $exception;
}
}
