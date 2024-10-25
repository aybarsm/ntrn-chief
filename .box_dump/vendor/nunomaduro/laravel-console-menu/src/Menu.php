<?php

declare(strict_types=1);










namespace NunoMaduro\LaravelConsoleMenu;

use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;




class Menu extends CliMenuBuilder
{





private $result;






public function __construct($title = '', array $options = [])
{
parent::__construct();

$this->addLineBreak(' ')
->setTitleSeparator('-');

$this->setMarginAuto();

$this->setTitle($title);

$this->addOptions($options);
}






public function addOption($value, string $label): Menu
{
$this->addMenuItem(
new MenuOption(
$value,
$label,
function (CliMenu $menu) {
$this->result = $menu->getSelectedItem()->getValue();
$menu->close();
}
)
);

return $this;
}




public function addOptions(array $options): Menu
{
foreach ($options as $value => $label) {
$this->addOption($value, $label);
}

return $this;
}




public function addQuestion(string $label, string $placeholder = ''): Menu
{
$itemCallable = function (CliMenu $menu) use ($label, $placeholder) {
$result = $menu->askText()
->setPromptText($label)
->setPlaceholderText($placeholder)
->ask();

$this->result = $result->fetch();

$menu->close();
};

$this->addItem($label, $itemCallable);

return $this;
}






public function open()
{
$this->build()
->open();

return $this->result;
}






public function setResult($result): Menu
{
$this->result = $result;

return $this;
}
}
