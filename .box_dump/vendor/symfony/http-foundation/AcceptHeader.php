<?php










namespace Symfony\Component\HttpFoundation;


class_exists(AcceptHeaderItem::class);









class AcceptHeader
{



private array $items = [];

private bool $sorted = true;




public function __construct(array $items)
{
foreach ($items as $item) {
$this->add($item);
}
}




public static function fromString(?string $headerValue): self
{
$parts = HeaderUtils::split($headerValue ?? '', ',;=');

return new self(array_map(function ($subParts) {
static $index = 0;
$part = array_shift($subParts);
$attributes = HeaderUtils::combine($subParts);

$item = new AcceptHeaderItem($part[0], $attributes);
$item->setIndex($index++);

return $item;
}, $parts));
}




public function __toString(): string
{
return implode(',', $this->items);
}




public function has(string $value): bool
{
return isset($this->items[$value]);
}




public function get(string $value): ?AcceptHeaderItem
{
return $this->items[$value] ?? $this->items[explode('/', $value)[0].'/*'] ?? $this->items['*/*'] ?? $this->items['*'] ?? null;
}






public function add(AcceptHeaderItem $item): static
{
$this->items[$item->getValue()] = $item;
$this->sorted = false;

return $this;
}






public function all(): array
{
$this->sort();

return $this->items;
}




public function filter(string $pattern): self
{
return new self(array_filter($this->items, fn (AcceptHeaderItem $item) => preg_match($pattern, $item->getValue())));
}




public function first(): ?AcceptHeaderItem
{
$this->sort();

return $this->items ? reset($this->items) : null;
}




private function sort(): void
{
if (!$this->sorted) {
uasort($this->items, function (AcceptHeaderItem $a, AcceptHeaderItem $b) {
$qA = $a->getQuality();
$qB = $b->getQuality();

if ($qA === $qB) {
return $a->getIndex() > $b->getIndex() ? 1 : -1;
}

return $qA > $qB ? -1 : 1;
});

$this->sorted = true;
}
}
}
