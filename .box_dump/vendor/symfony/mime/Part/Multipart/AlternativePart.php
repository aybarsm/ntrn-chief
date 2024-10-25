<?php










namespace Symfony\Component\Mime\Part\Multipart;

use Symfony\Component\Mime\Part\AbstractMultipartPart;




final class AlternativePart extends AbstractMultipartPart
{
public function getMediaSubtype(): string
{
return 'alternative';
}
}
