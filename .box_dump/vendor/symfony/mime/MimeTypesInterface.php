<?php










namespace Symfony\Component\Mime;




interface MimeTypesInterface extends MimeTypeGuesserInterface
{





public function getExtensions(string $mimeType): array;






public function getMimeTypes(string $ext): array;
}
