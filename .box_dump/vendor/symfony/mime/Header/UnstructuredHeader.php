<?php










namespace Symfony\Component\Mime\Header;






class UnstructuredHeader extends AbstractHeader
{
private string $value;

public function __construct(string $name, string $value)
{
parent::__construct($name);

$this->setValue($value);
}




public function setBody(mixed $body): void
{
$this->setValue($body);
}

public function getBody(): string
{
return $this->getValue();
}




public function getValue(): string
{
return $this->value;
}




public function setValue(string $value): void
{
$this->value = $value;
}




public function getBodyAsString(): string
{
return $this->encodeWords($this, $this->value);
}
}
