<?php

namespace Illuminate\Http\Concerns;

use Illuminate\Support\Str;

trait InteractsWithContentTypes
{





public function isJson()
{
return Str::contains($this->header('CONTENT_TYPE') ?? '', ['/json', '+json']);
}






public function expectsJson()
{
return ($this->ajax() && ! $this->pjax() && $this->acceptsAnyContentType()) || $this->wantsJson();
}






public function wantsJson()
{
$acceptable = $this->getAcceptableContentTypes();

return isset($acceptable[0]) && Str::contains(strtolower($acceptable[0]), ['/json', '+json']);
}







public function accepts($contentTypes)
{
$accepts = $this->getAcceptableContentTypes();

if (count($accepts) === 0) {
return true;
}

$types = (array) $contentTypes;

foreach ($accepts as $accept) {
if ($accept === '*/*' || $accept === '*') {
return true;
}

foreach ($types as $type) {
$accept = strtolower($accept);

$type = strtolower($type);

if ($this->matchesType($accept, $type) || $accept === strtok($type, '/').'/*') {
return true;
}
}
}

return false;
}







public function prefers($contentTypes)
{
$accepts = $this->getAcceptableContentTypes();

$contentTypes = (array) $contentTypes;

foreach ($accepts as $accept) {
if (in_array($accept, ['*/*', '*'])) {
return $contentTypes[0];
}

foreach ($contentTypes as $contentType) {
$type = $contentType;

if (! is_null($mimeType = $this->getMimeType($contentType))) {
$type = $mimeType;
}

$accept = strtolower($accept);

$type = strtolower($type);

if ($this->matchesType($type, $accept) || $accept === strtok($type, '/').'/*') {
return $contentType;
}
}
}
}






public function acceptsAnyContentType()
{
$acceptable = $this->getAcceptableContentTypes();

return count($acceptable) === 0 || (
isset($acceptable[0]) && ($acceptable[0] === '*/*' || $acceptable[0] === '*')
);
}






public function acceptsJson()
{
return $this->accepts('application/json');
}






public function acceptsHtml()
{
return $this->accepts('text/html');
}








public static function matchesType($actual, $type)
{
if ($actual === $type) {
return true;
}

$split = explode('/', $actual);

return isset($split[1]) && preg_match('#'.preg_quote($split[0], '#').'/.+\+'.preg_quote($split[1], '#').'#', $type);
}







public function format($default = 'html')
{
foreach ($this->getAcceptableContentTypes() as $type) {
if ($format = $this->getFormat($type)) {
return $format;
}
}

return $default;
}
}
