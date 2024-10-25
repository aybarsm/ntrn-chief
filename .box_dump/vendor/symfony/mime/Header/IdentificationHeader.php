<?php










namespace Symfony\Component\Mime\Header;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Exception\RfcComplianceException;






final class IdentificationHeader extends AbstractHeader
{
private array $ids = [];
private array $idsAsAddresses = [];

public function __construct(string $name, string|array $ids)
{
parent::__construct($name);

$this->setId($ids);
}






public function setBody(mixed $body): void
{
$this->setId($body);
}

public function getBody(): array
{
return $this->getIds();
}








public function setId(string|array $id): void
{
$this->setIds(\is_array($id) ? $id : [$id]);
}






public function getId(): ?string
{
return $this->ids[0] ?? null;
}








public function setIds(array $ids): void
{
$this->ids = [];
$this->idsAsAddresses = [];
foreach ($ids as $id) {
$this->idsAsAddresses[] = new Address($id);
$this->ids[] = $id;
}
}






public function getIds(): array
{
return $this->ids;
}

public function getBodyAsString(): string
{
$addrs = [];
foreach ($this->idsAsAddresses as $address) {
$addrs[] = '<'.$address->toString().'>';
}

return implode(' ', $addrs);
}
}
