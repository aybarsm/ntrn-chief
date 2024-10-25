<?php










namespace Symfony\Component\Mime\Encoder;




final class QpMimeHeaderEncoder extends QpEncoder implements MimeHeaderEncoderInterface
{
protected function initSafeMap(): void
{
foreach (array_merge(
range(0x61, 0x7A), range(0x41, 0x5A),
range(0x30, 0x39), [0x20, 0x21, 0x2A, 0x2B, 0x2D, 0x2F]
) as $byte) {
$this->safeMap[$byte] = \chr($byte);
}
}

public function getName(): string
{
return 'Q';
}

public function encodeString(string $string, ?string $charset = 'utf-8', int $firstLineOffset = 0, int $maxLineLength = 0): string
{
return str_replace([' ', '=20', "=\r\n"], ['_', '_', "\r\n"],
parent::encodeString($string, $charset, $firstLineOffset, $maxLineLength)
);
}
}
