<?php










namespace Symfony\Component\Mime\Encoder;




class Base64Encoder implements EncoderInterface
{







public function encodeString(string $string, ?string $charset = 'utf-8', int $firstLineOffset = 0, int $maxLineLength = 0): string
{
if (0 >= $maxLineLength || 76 < $maxLineLength) {
$maxLineLength = 76;
}

$encodedString = base64_encode($string);
$firstLine = '';
if (0 !== $firstLineOffset) {
$firstLine = substr($encodedString, 0, $maxLineLength - $firstLineOffset)."\r\n";
$encodedString = substr($encodedString, $maxLineLength - $firstLineOffset);
}

return $firstLine.trim(chunk_split($encodedString, $maxLineLength, "\r\n"));
}
}
