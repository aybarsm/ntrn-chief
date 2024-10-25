<?php

namespace Laravel\Prompts;

use Closure;
use Laravel\Prompts\Support\Utils;

class MultiSearchPrompt extends Prompt
{
use Concerns\Scrolling;
use Concerns\Truncation;
use Concerns\TypedValue;






protected ?array $matches = null;




protected bool $isList;






public array $values = [];






public function __construct(
public string $label,
public Closure $options,
public string $placeholder = '',
public int $scroll = 5,
public bool|string $required = false,
public mixed $validate = null,
public string $hint = '',
public ?Closure $transform = null,
) {
$this->trackTypedValue(submit: false, ignore: fn ($key) => Key::oneOf([Key::SPACE, Key::HOME, Key::END, Key::CTRL_A, Key::CTRL_E], $key) && $this->highlighted !== null);

$this->initializeScrolling(null);

$this->on('key', fn ($key) => match ($key) {
Key::UP, Key::UP_ARROW, Key::SHIFT_TAB => $this->highlightPrevious(count($this->matches), true),
Key::DOWN, Key::DOWN_ARROW, Key::TAB => $this->highlightNext(count($this->matches), true),
Key::oneOf(Key::HOME, $key) => $this->highlighted !== null ? $this->highlight(0) : null,
Key::oneOf(Key::END, $key) => $this->highlighted !== null ? $this->highlight(count($this->matches()) - 1) : null,
Key::SPACE => $this->highlighted !== null ? $this->toggleHighlighted() : null,
Key::CTRL_A => $this->highlighted !== null ? $this->toggleAll() : null,
Key::CTRL_E => null,
Key::ENTER => $this->submit(),
Key::LEFT, Key::LEFT_ARROW, Key::RIGHT, Key::RIGHT_ARROW => $this->highlighted = null,
default => $this->search(),
});
}




protected function search(): void
{
$this->state = 'searching';
$this->highlighted = null;
$this->render();
$this->matches = null;
$this->firstVisible = 0;
$this->state = 'active';
}




public function valueWithCursor(int $maxWidth): string
{
if ($this->highlighted !== null) {
return $this->typedValue === ''
? $this->dim($this->truncate($this->placeholder, $maxWidth))
: $this->truncate($this->typedValue, $maxWidth);
}

if ($this->typedValue === '') {
return $this->dim($this->addCursor($this->placeholder, 0, $maxWidth));
}

return $this->addCursor($this->typedValue, $this->cursorPosition, $maxWidth);
}






public function matches(): array
{
if (is_array($this->matches)) {
return $this->matches;
}

$matches = ($this->options)($this->typedValue);

if (! isset($this->isList) && count($matches) > 0) {


$this->isList = array_is_list($matches);
}

if (! isset($this->isList)) {
return $this->matches = [];
}

if (strlen($this->typedValue) > 0) {
return $this->matches = $matches;
}

return $this->matches = $this->isList
? [...array_diff(array_values($this->values), $matches), ...$matches]
: array_diff($this->values, $matches) + $matches;
}






public function visible(): array
{
return array_slice($this->matches(), $this->firstVisible, $this->scroll, preserve_keys: true);
}




protected function toggleAll(): void
{
$allMatchesSelected = Utils::allMatch($this->matches, fn ($label, $key) => $this->isList()
? array_key_exists($label, $this->values)
: array_key_exists($key, $this->values));

if ($allMatchesSelected) {
$this->values = array_filter($this->values, fn ($value) => $this->isList()
? ! in_array($value, $this->matches)
: ! array_key_exists(array_search($value, $this->matches), $this->matches)
);
} else {
$this->values = $this->isList()
? array_merge($this->values, array_combine(array_values($this->matches), array_values($this->matches)))
: array_merge($this->values, array_combine(array_keys($this->matches), array_values($this->matches)));
}
}




protected function toggleHighlighted(): void
{
if ($this->isList()) {
$label = $this->matches[$this->highlighted];
$key = $label;
} else {
$key = array_keys($this->matches)[$this->highlighted];
$label = $this->matches[$key];
}

if (array_key_exists($key, $this->values)) {
unset($this->values[$key]);
} else {
$this->values[$key] = $label;
}
}




public function searchValue(): string
{
return $this->typedValue;
}






public function value(): array
{
return array_keys($this->values);
}






public function labels(): array
{
return array_values($this->values);
}




public function isList(): bool
{
return $this->isList;
}
}