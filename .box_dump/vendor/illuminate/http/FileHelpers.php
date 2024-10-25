<?php

namespace Illuminate\Http;

use Illuminate\Support\Str;

trait FileHelpers
{





protected $hashName = null;






public function path()
{
return $this->getRealPath();
}






public function extension()
{
return $this->guessExtension();
}







public function hashName($path = null)
{
if ($path) {
$path = rtrim($path, '/').'/';
}

$hash = $this->hashName ?: $this->hashName = Str::random(40);

if ($extension = $this->guessExtension()) {
$extension = '.'.$extension;
}

return $path.$hash.$extension;
}






public function dimensions()
{
return @getimagesize($this->getRealPath());
}
}
