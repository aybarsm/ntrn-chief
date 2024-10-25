<?php

namespace Illuminate\Http\Client\Events;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;

class ResponseReceived
{





public $request;






public $response;








public function __construct(Request $request, Response $response)
{
$this->request = $request;
$this->response = $response;
}
}
