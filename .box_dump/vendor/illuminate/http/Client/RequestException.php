<?php

namespace Illuminate\Http\Client;

use GuzzleHttp\Psr7\Message;

class RequestException extends HttpClientException
{





public $response;







public function __construct(Response $response)
{
parent::__construct($this->prepareMessage($response), $response->status());

$this->response = $response;
}







protected function prepareMessage(Response $response)
{
$message = "HTTP request returned status code {$response->status()}";

$summary = Message::bodySummary($response->toPsrResponse());

return is_null($summary) ? $message : $message .= ":\n{$summary}\n";
}
}
