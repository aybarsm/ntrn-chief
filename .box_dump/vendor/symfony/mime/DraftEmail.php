<?php










namespace Symfony\Component\Mime;

use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;




class DraftEmail extends Email
{
public function __construct(?Headers $headers = null, ?AbstractPart $body = null)
{
parent::__construct($headers, $body);

$this->getHeaders()->addTextHeader('X-Unsent', '1');
}





public function getPreparedHeaders(): Headers
{
$headers = clone $this->getHeaders();

if (!$headers->has('MIME-Version')) {
$headers->addTextHeader('MIME-Version', '1.0');
}

$headers->remove('Bcc');

return $headers;
}
}
