<?php










namespace Symfony\Component\Mime\Part\Multipart;

use Symfony\Component\Mime\Part\AbstractMultipartPart;
use Symfony\Component\Mime\Part\MessagePart;




final class DigestPart extends AbstractMultipartPart
{
public function __construct(MessagePart ...$parts)
{
parent::__construct(...$parts);
}

public function getMediaSubtype(): string
{
return 'digest';
}
}
