<?php










namespace Symfony\Component\Mime\Header;






final class DateHeader extends AbstractHeader
{
private \DateTimeImmutable $dateTime;

public function __construct(string $name, \DateTimeInterface $date)
{
parent::__construct($name);

$this->setDateTime($date);
}




public function setBody(mixed $body): void
{
$this->setDateTime($body);
}

public function getBody(): \DateTimeImmutable
{
return $this->getDateTime();
}

public function getDateTime(): \DateTimeImmutable
{
return $this->dateTime;
}






public function setDateTime(\DateTimeInterface $dateTime): void
{
$this->dateTime = \DateTimeImmutable::createFromInterface($dateTime);
}

public function getBodyAsString(): string
{
return $this->dateTime->format(\DateTimeInterface::RFC2822);
}
}
