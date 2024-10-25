<?php










namespace Symfony\Component\Mime\Encoder;

use Symfony\Component\Mime\CharacterStream;




final class Rfc2231Encoder implements EncoderInterface
{



public function encodeString(string $string, ?string $charset = 'utf-8', int $firstLineOffset = 0, int $maxLineLength = 0): string
{
$lines = [];
$lineCount = 0;
$lines[] = '';
$currentLine = &$lines[$lineCount++];

if (0 >= $maxLineLength) {
$maxLineLength = 75;
}

$charStream = new CharacterStream($string, $charset);
$thisLineLength = $maxLineLength - $firstLineOffset;

while (null !== $char = $charStream->read(4)) {
$encodedChar = rawurlencode($char);
if ('' !== $currentLine && \strlen($currentLine.$encodedChar) > $thisLineLength) {
$lines[] = '';
$currentLine = &$lines[$lineCount++];
$thisLineLength = $maxLineLength;
}
$currentLine .= $encodedChar;
}

return implode("\r\n", $lines);
}
}
