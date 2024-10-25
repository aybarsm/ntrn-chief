<?php










namespace Symfony\Component\Mime\Encoder;












final class IdnAddressEncoder implements AddressEncoderInterface
{



public function encodeString(string $address): string
{
$i = strrpos($address, '@');
if (false !== $i) {
$local = substr($address, 0, $i);
$domain = substr($address, $i + 1);

if (preg_match('/[^\x00-\x7F]/', $domain)) {
$address = sprintf('%s@%s', $local, idn_to_ascii($domain, \IDNA_DEFAULT | \IDNA_USE_STD3_RULES | \IDNA_CHECK_BIDI | \IDNA_CHECK_CONTEXTJ | \IDNA_NONTRANSITIONAL_TO_ASCII, \INTL_IDNA_VARIANT_UTS46));
}
}

return $address;
}
}
