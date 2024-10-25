<?php
declare(strict_types=1);

namespace PhpSchool\CliMenu\Input;




class InputResult
{



private $input;

public function __construct(string $input)
{
$this->input = $input;
}

public function fetch() : string
{
return $this->input;
}
}
