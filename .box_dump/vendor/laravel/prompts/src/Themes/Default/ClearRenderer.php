<?php

namespace Laravel\Prompts\Themes\Default;

class ClearRenderer extends Renderer
{



public function __invoke(): string
{
return "\033[H\033[J";
}
}