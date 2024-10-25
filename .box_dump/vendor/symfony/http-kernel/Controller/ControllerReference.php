<?php










namespace Symfony\Component\HttpKernel\Controller;

use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;












class ControllerReference
{
public array $attributes = [];
public array $query = [];






public function __construct(
public string $controller,
array $attributes = [],
array $query = [],
) {
$this->attributes = $attributes;
$this->query = $query;
}
}
