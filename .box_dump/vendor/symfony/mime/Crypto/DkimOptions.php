<?php










namespace Symfony\Component\Mime\Crypto;






final class DkimOptions
{
private array $options = [];

public function toArray(): array
{
return $this->options;
}




public function algorithm(string $algo): static
{
$this->options['algorithm'] = $algo;

return $this;
}




public function signatureExpirationDelay(int $show): static
{
$this->options['signature_expiration_delay'] = $show;

return $this;
}




public function bodyMaxLength(int $max): static
{
$this->options['body_max_length'] = $max;

return $this;
}




public function bodyShowLength(bool $show): static
{
$this->options['body_show_length'] = $show;

return $this;
}




public function headerCanon(string $canon): static
{
$this->options['header_canon'] = $canon;

return $this;
}




public function bodyCanon(string $canon): static
{
$this->options['body_canon'] = $canon;

return $this;
}




public function headersToIgnore(array $headers): static
{
$this->options['headers_to_ignore'] = $headers;

return $this;
}
}
