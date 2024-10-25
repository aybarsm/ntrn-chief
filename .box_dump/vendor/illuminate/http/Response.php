<?php

namespace Illuminate\Http;

use ArrayObject;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use JsonSerializable;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Response extends SymfonyResponse
{
use ResponseTrait, Macroable {
Macroable::__call as macroCall;
}











public function __construct($content = '', $status = 200, array $headers = [])
{
$this->headers = new ResponseHeaderBag($headers);

$this->setContent($content);
$this->setStatusCode($status);
$this->setProtocolVersion('1.0');
}









#[\Override]
public function setContent(mixed $content): static
{
$this->original = $content;




if ($this->shouldBeJson($content)) {
$this->header('Content-Type', 'application/json');

$content = $this->morphToJson($content);

if ($content === false) {
throw new InvalidArgumentException(json_last_error_msg());
}
}




elseif ($content instanceof Renderable) {
$content = $content->render();
}

parent::setContent($content);

return $this;
}







protected function shouldBeJson($content)
{
return $content instanceof Arrayable ||
$content instanceof Jsonable ||
$content instanceof ArrayObject ||
$content instanceof JsonSerializable ||
is_array($content);
}







protected function morphToJson($content)
{
if ($content instanceof Jsonable) {
return $content->toJson();
} elseif ($content instanceof Arrayable) {
return json_encode($content->toArray());
}

return json_encode($content);
}
}
