<?php

declare(strict_types=1);










namespace NunoMaduro\LaravelConsoleMenu;

use PhpSchool\CliMenu\MenuItem\SelectableItem;




class MenuOption extends SelectableItem
{





private $value;









public function __construct($value, $text, callable $callback, $showItemExtra = false, $disabled = false)
{
parent::__construct($text, $callback, $showItemExtra, $disabled);

$this->value = $value;
}






public function getValue()
{
return $this->value;
}
}
