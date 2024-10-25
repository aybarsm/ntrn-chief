<?php










namespace Symfony\Component\Mime\Encoder;




final class Base64MimeHeaderEncoder extends Base64Encoder implements MimeHeaderEncoderInterface
{
public function getName(): string
{
return 'B';
}







public function encodeString(string $string, ?string $charset = 'utf-8', int $firstLineOffset = 0, int $maxLineLength = 0): string
{
if ('iso-2022-jp' === strtolower($charset)) {
$old = mb_internal_encoding();
mb_internal_encoding('utf-8');
$newstring = mb_encode_mimeheader($string, 'iso-2022-jp', $this->getName(), "\r\n");
mb_internal_encoding($old);

return $newstring;
}

return parent::encodeString($string, $charset, $firstLineOffset, $maxLineLength);
}
}
