<?php
declare(strict_types=1);

namespace PhpSchool\CliMenu;







class Frame implements \Countable
{



private $rows = [];

public function newLine(int $count = 1) : void
{
foreach (range(1, $count) as $i) {
$this->rows[] = "\n";
}
}

public function addRows(array $rows = []) : void
{
foreach ($rows as $row) {
$this->rows[] = $row;
}
}

public function addRow(string $row) : void
{
$this->rows[] = $row;
}

public function count() : int
{
return count($this->rows);
}

public function getRows() : array
{
return $this->rows;
}
}
