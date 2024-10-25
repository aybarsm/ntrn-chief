<?php

































namespace PHPUnit\Runner\Filter;

use Exception;
use Pest\Contracts\HasPrintableTestCaseName;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use RecursiveFilterIterator;
use RecursiveIterator;

use function end;
use function implode;
use function preg_match;
use function sprintf;
use function str_replace;




final class NameFilterIterator extends RecursiveFilterIterator
{
private ?string $filter = null;

private ?int $filterMin = null;

private ?int $filterMax = null;




public function __construct(RecursiveIterator $iterator, string $filter)
{
parent::__construct($iterator);

$this->setFilter($filter);
}

public function accept(): bool
{
$test = $this->getInnerIterator()->current();

if ($test instanceof TestSuite) {
return true;
}

$tmp = $this->describe($test);

if ($tmp[0] !== '') {
$name = implode('::', $tmp);
} else {
$name = $tmp[1];
}

$accepted = @preg_match($this->filter, $name, $matches);

if ($accepted && isset($this->filterMax)) {
$set = end($matches);
$accepted = $set >= $this->filterMin && $set <= $this->filterMax;
}

return (bool) $accepted;
}




private function setFilter(string $filter): void
{
if (@preg_match($filter, '') === false) {



if (preg_match('/^(.*?)#(\d+)(?:-(\d+))?$/', $filter, $matches)) {
if (isset($matches[3]) && $matches[2] < $matches[3]) {
$filter = sprintf(
'%s.*with dataset #(\d+)$',
$matches[1]
);

$this->filterMin = (int) $matches[2];
$this->filterMax = (int) $matches[3];
} else {
$filter = sprintf(
'%s.*with dataset #%s$',
$matches[1],
$matches[2]
);
}
} 


elseif (preg_match('/^(.*?)@(.+)$/', $filter, $matches)) {
$filter = sprintf(
'%s.*with dataset "%s"$',
$matches[1],
$matches[2]
);
}



$filter = sprintf(
'/%s/i',
str_replace(
'/',
'\\/',
$filter
)
);
}

$this->filter = $filter;
}

/**
@psalm-return
*/
private function describe(Test $test): array
{
if ($test instanceof HasPrintableTestCaseName) {
return [
$test::getPrintableTestCaseName(),
$test->getPrintableTestCaseMethodName(),
];
}

if ($test instanceof TestCase) {
return [$test::class, $test->nameWithDataSet()];
}

if ($test instanceof SelfDescribing) {
return ['', $test->toString()];
}

return ['', $test::class];
}
}