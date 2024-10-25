<?php

namespace Illuminate\Foundation\Exceptions\Whoops;

use Illuminate\Contracts\Foundation\ExceptionRenderer;
use Whoops\Run as Whoops;

use function tap;

class WhoopsExceptionRenderer implements ExceptionRenderer
{






public function render($throwable)
{
return tap(new Whoops, function ($whoops) {
$whoops->appendHandler($this->whoopsHandler());

$whoops->writeToOutput(false);

$whoops->allowQuit(false);
})->handleException($throwable);
}






protected function whoopsHandler()
{
return (new WhoopsHandler)->forDebug();
}
}
