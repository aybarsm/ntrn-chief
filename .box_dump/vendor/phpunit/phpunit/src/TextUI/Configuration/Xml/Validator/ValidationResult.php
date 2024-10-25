<?php declare(strict_types=1);








namespace PHPUnit\TextUI\XmlConfiguration;

use const PHP_EOL;
use function sprintf;
use function trim;
use LibXMLError;

/**
@no-named-arguments
@psalm-immutable



*/
final class ValidationResult
{
/**
@psalm-var
*/
private readonly array $validationErrors;

/**
@psalm-param
*/
public static function fromArray(array $errors): self
{
$validationErrors = [];

foreach ($errors as $error) {
if (!isset($validationErrors[$error->line])) {
$validationErrors[$error->line] = [];
}

$validationErrors[$error->line][] = trim($error->message);
}

return new self($validationErrors);
}

private function __construct(array $validationErrors)
{
$this->validationErrors = $validationErrors;
}

public function hasValidationErrors(): bool
{
return !empty($this->validationErrors);
}

public function asString(): string
{
$buffer = '';

foreach ($this->validationErrors as $line => $validationErrorsOnLine) {
$buffer .= sprintf(PHP_EOL . '  Line %d:' . PHP_EOL, $line);

foreach ($validationErrorsOnLine as $validationError) {
$buffer .= sprintf('  - %s' . PHP_EOL, $validationError);
}
}

return $buffer;
}
}
