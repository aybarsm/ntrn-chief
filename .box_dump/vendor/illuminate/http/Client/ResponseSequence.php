<?php

namespace Illuminate\Http\Client;

use Illuminate\Support\Traits\Macroable;
use OutOfBoundsException;

class ResponseSequence
{
use Macroable;






protected $responses;






protected $failWhenEmpty = true;






protected $emptyResponse;







public function __construct(array $responses)
{
$this->responses = $responses;
}









public function push($body = null, int $status = 200, array $headers = [])
{
return $this->pushResponse(
Factory::response($body, $status, $headers)
);
}








public function pushStatus(int $status, array $headers = [])
{
return $this->pushResponse(
Factory::response('', $status, $headers)
);
}









public function pushFile(string $filePath, int $status = 200, array $headers = [])
{
$string = file_get_contents($filePath);

return $this->pushResponse(
Factory::response($string, $status, $headers)
);
}







public function pushResponse($response)
{
$this->responses[] = $response;

return $this;
}







public function whenEmpty($response)
{
$this->failWhenEmpty = false;
$this->emptyResponse = $response;

return $this;
}






public function dontFailWhenEmpty()
{
return $this->whenEmpty(Factory::response());
}






public function isEmpty()
{
return count($this->responses) === 0;
}








public function __invoke()
{
if ($this->failWhenEmpty && $this->isEmpty()) {
throw new OutOfBoundsException('A request was made, but the response sequence is empty.');
}

if (! $this->failWhenEmpty && $this->isEmpty()) {
return value($this->emptyResponse ?? Factory::response());
}

return array_shift($this->responses);
}
}
