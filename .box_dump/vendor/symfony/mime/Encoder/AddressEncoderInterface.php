<?php










namespace Symfony\Component\Mime\Encoder;

use Symfony\Component\Mime\Exception\AddressEncoderException;




interface AddressEncoderInterface
{






public function encodeString(string $address): string;
}
