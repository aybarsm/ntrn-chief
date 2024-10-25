<?php

namespace Faker\Extension;

/**
@experimental
*/
interface ColorExtension extends Extension
{



public function hexColor(): string;




public function safeHexColor(): string;






public function rgbColorAsArray(): array;




public function rgbColor(): string;




public function rgbCssColor(): string;




public function rgbaCssColor(): string;




public function safeColorName(): string;




public function colorName(): string;




public function hslColor(): string;






public function hslColorAsArray(): array;
}
