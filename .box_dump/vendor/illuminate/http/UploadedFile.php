<?php

namespace Illuminate\Http;

use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Testing\FileFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

class UploadedFile extends SymfonyUploadedFile
{
use FileHelpers, Macroable;






public static function fake()
{
return new FileFactory;
}








public function store($path = '', $options = [])
{
return $this->storeAs($path, $this->hashName(), $this->parseOptions($options));
}








public function storePublicly($path = '', $options = [])
{
$options = $this->parseOptions($options);

$options['visibility'] = 'public';

return $this->storeAs($path, $this->hashName(), $options);
}









public function storePubliclyAs($path, $name = null, $options = [])
{
if (is_null($name) || is_array($name)) {
[$path, $name, $options] = ['', $path, $name ?? []];
}

$options = $this->parseOptions($options);

$options['visibility'] = 'public';

return $this->storeAs($path, $name, $options);
}









public function storeAs($path, $name = null, $options = [])
{
if (is_null($name) || is_array($name)) {
[$path, $name, $options] = ['', $path, $name ?? []];
}

$options = $this->parseOptions($options);

$disk = Arr::pull($options, 'disk');

return Container::getInstance()->make(FilesystemFactory::class)->disk($disk)->putFileAs(
$path, $this, $name, $options
);
}








public function get()
{
if (! $this->isValid()) {
throw new FileNotFoundException("File does not exist at path {$this->getPathname()}.");
}

return file_get_contents($this->getPathname());
}






public function clientExtension()
{
return $this->guessClientExtension();
}








public static function createFromBase(SymfonyUploadedFile $file, $test = false)
{
return $file instanceof static ? $file : new static(
$file->getPathname(),
$file->getClientOriginalName(),
$file->getClientMimeType(),
$file->getError(),
$test
);
}







protected function parseOptions($options)
{
if (is_string($options)) {
$options = ['disk' => $options];
}

return $options;
}
}
