<?php
declare(strict_types=1);

namespace PhpSchool\CliMenu\Input;

use PhpSchool\CliMenu\MenuStyle;




class Password implements Input
{



private $inputIO;




private $promptText = 'Enter password:';




private $validationFailedText = 'Invalid password, try again';




private $placeholderText = '';




private $validator;




private $style;




private $passwordLength = 16;

public function __construct(InputIO $inputIO, MenuStyle $style)
{
$this->inputIO = $inputIO;
$this->style = $style;
}

public function setPromptText(string $promptText) : Input
{
$this->promptText = $promptText;

return $this;
}

public function getPromptText() : string
{
return $this->promptText;
}

public function setValidationFailedText(string $validationFailedText) : Input
{
$this->validationFailedText = $validationFailedText;

return $this;
}

public function getValidationFailedText() : string
{
return $this->validationFailedText;
}

public function setPlaceholderText(string $placeholderText) : Input
{
$this->placeholderText = $placeholderText;

return $this;
}

public function getPlaceholderText() : string
{
return $this->placeholderText;
}

public function setValidator(callable $validator) : Input
{
$this->validator = $validator;

return $this;
}

public function ask() : InputResult
{
return $this->inputIO->collect($this);
}

public function validate(string $input) : bool
{
if ($this->validator) {
$validator = $this->validator;

if ($validator instanceof \Closure) {
$validator = $validator->bindTo($this);
}

return $validator($input);
}

return mb_strlen($input) >= $this->passwordLength;
}

public function filter(string $value) : string
{
return str_repeat('*', mb_strlen($value));
}

public function getStyle() : MenuStyle
{
return $this->style;
}

public function setPasswordLength(int $length) : int
{
return $this->passwordLength = $length;
}
}
