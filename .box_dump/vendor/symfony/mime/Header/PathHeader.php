<?php










namespace Symfony\Component\Mime\Header;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Exception\RfcComplianceException;






final class PathHeader extends AbstractHeader
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
return '<'.$this->address->toString().'>';
}
}
