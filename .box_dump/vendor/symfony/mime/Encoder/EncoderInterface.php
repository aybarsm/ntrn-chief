<?php










namespace Symfony\Component\Mime\Encoder;




interface EncoderInterface
{






public function encodeString(string $string, ?string $charset = 'utf-8', int $firstLineOffset = 0, int $maxLineLength = 0): string;
}
