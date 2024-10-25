<?php

namespace Illuminate\Database\Query;

use Illuminate\Contracts\Database\Query\Expression as ExpressionContract;
use Illuminate\Database\Grammar;

class Expression implements ExpressionContract
{





protected $value;







public function __construct($value)
{
$this->value = $value;
}







public function getValue(Grammar $grammar)
{
return $this->value;
}
}
