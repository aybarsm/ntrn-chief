<?php










namespace Symfony\Component\Mime\Header;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Exception\RfcComplianceException;






final class MailboxListHeader extends AbstractHeader
{
private array $addresses = [];




public function __construct(string $name, array $addresses)
{
parent::__construct($name);

$this->setAddresses($addresses);
}






public function setBody(mixed $body): void
{
$this->setAddresses($body);
}






public function getBody(): array
{
return $this->getAddresses();
}








public function setAddresses(array $addresses): void
{
$this->addresses = [];
$this->addAddresses($addresses);
}








public function addAddresses(array $addresses): void
{
foreach ($addresses as $address) {
$this->addAddress($address);
}
}




public function addAddress(Address $address): void
{
$this->addresses[] = $address;
}




public function getAddresses(): array
{
return $this->addresses;
}








public function getAddressStrings(): array
{
$strings = [];
foreach ($this->addresses as $address) {
$str = $address->getEncodedAddress();
if ($name = $address->getName()) {
$str = $this->createPhrase($this, $name, $this->getCharset(), !$strings).' <'.$str.'>';
}
$strings[] = $str;
}

return $strings;
}

public function getBodyAsString(): string
{
return implode(', ', $this->getAddressStrings());
}








protected function tokenNeedsEncoding(string $token): bool
{
return preg_match('/[()<>\[\]:;@\,."]/', $token) || parent::tokenNeedsEncoding($token);
}
}
