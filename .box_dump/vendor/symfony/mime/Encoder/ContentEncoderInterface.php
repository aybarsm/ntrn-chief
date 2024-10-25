<?php










namespace Symfony\Component\Mime\Encoder;




interface ContentEncoderInterface extends EncoderInterface
{





public function encodeByteStream($stream, int $maxLineLength = 0): iterable;




public function getName(): string;
}
