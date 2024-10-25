<?php declare(strict_types=1);








namespace PHPUnit\Runner\Baseline;

/**
@no-named-arguments


*/
abstract class Subscriber
{
private readonly Generator $generator;

public function __construct(Generator $generator)
{
$this->generator = $generator;
}

protected function generator(): Generator
{
return $this->generator;
}
}
