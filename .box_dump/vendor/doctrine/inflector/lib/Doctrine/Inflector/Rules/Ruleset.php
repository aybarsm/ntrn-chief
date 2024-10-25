<?php

declare(strict_types=1);

namespace Doctrine\Inflector\Rules;

class Ruleset
{

private $regular;


private $uninflected;


private $irregular;

public function __construct(Transformations $regular, Patterns $uninflected, Substitutions $irregular)
{
$this->regular = $regular;
$this->uninflected = $uninflected;
$this->irregular = $irregular;
}

public function getRegular(): Transformations
{
return $this->regular;
}

public function getUninflected(): Patterns
{
return $this->uninflected;
}

public function getIrregular(): Substitutions
{
return $this->irregular;
}
}
