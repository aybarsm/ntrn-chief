<?php










namespace Symfony\Component\Mime\Part\Multipart;

use Symfony\Component\Mime\Part\AbstractMultipartPart;




final class MixedPart extends AbstractMultipartPart
{
public function getMediaSubtype(): string
{
return 'mixed';
}
}
