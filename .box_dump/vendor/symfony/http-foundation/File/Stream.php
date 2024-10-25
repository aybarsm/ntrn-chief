<?php










namespace Symfony\Component\HttpFoundation\File;






class Stream extends File
{
public function getSize(): int|false
{
return false;
}
}
