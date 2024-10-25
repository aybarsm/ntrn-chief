<?php










namespace Symfony\Component\Mime\Encoder;




final class EightBitContentEncoder implements ContentEncoderInterface
{
public function encodeByteStream($stream, int $maxLineLength = 0): iterable
{
while (!feof($stream)) {
yield fread($stream, 16372);
}
}

public function getName(): string
{
return '8bit';
}

public function encodeString(string $string, ?string $charset = 'utf-8', int $firstLineOffset = 0, int $maxLineLength = 0): string
{
return $string;
}
}
