<?php










namespace Symfony\Component\Finder\Iterator;

use Symfony\Component\Finder\SplFileInfo;

/**
@extends





*/
class PathFilterIterator extends MultiplePcreFilterIterator
{



public function accept(): bool
{
$filename = $this->current()->getRelativePathname();

if ('\\' === \DIRECTORY_SEPARATOR) {
$filename = str_replace('\\', '/', $filename);
}

return $this->isAccepted($filename);
}













protected function toRegex(string $str): string
{
return $this->isRegex($str) ? $str : '/'.preg_quote($str, '/').'/';
}
}
