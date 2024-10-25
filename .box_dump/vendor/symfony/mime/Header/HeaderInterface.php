<?php










namespace Symfony\Component\Mime\Header;






interface HeaderInterface
{





public function setBody(mixed $body): void;






public function getBody(): mixed;

public function setCharset(string $charset): void;

public function getCharset(): ?string;

public function setLanguage(string $lang): void;

public function getLanguage(): ?string;

public function getName(): string;

public function setMaxLineLength(int $lineLength): void;

public function getMaxLineLength(): int;




public function toString(): string;







public function getBodyAsString(): string;
}
