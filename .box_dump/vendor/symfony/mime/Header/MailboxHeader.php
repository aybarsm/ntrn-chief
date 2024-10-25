<?php










namespace Symfony\Component\Mime\Header;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Exception\RfcComplianceException;






final class MailboxHeader extends AbstractHeader
{
private Address $address;

public function __construct(string $name, Address $address)
{
parent::__construct($name);

$this->setAddress($address);
}






public function setBody(mixed $body): void
{
$this->setAddress($body);
}




public function getBody(): Address
{
return $this->getAddress();
}




public function setAddress(Address $address): void
{
$this->address = $address;
}

public function getAddress(): Address
{
return $this->address;
}

public function getBodyAsString(): string
{
$str = $this->address->getEncodedAddress();
if ($name = $this->address->getName()) {
$str = $this->createPhrase($this, $name, $this->getCharset(), true).' <'.$str.'>';
}

return $str;
}








protected function tokenNeedsEncoding(string $token): bool
{
return preg_match('/[()<>\[\]:;@\,."]/', $token) || parent::tokenNeedsEncoding($token);
}
}
