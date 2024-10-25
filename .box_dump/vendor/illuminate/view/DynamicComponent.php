<?php

namespace Illuminate\View;

use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\ComponentTagCompiler;

class DynamicComponent extends Component
{





public $component;






protected static $compiler;






protected static $componentClasses = [];







public function __construct(string $component)
{
$this->component = $component;
}






public function render()
{
$template = <<<'EOF'
<?php extract(collect($attributes->getAttributes())->mapWithKeys(function ($value, $key) { return [Illuminate\Support\Str::camel(str_replace([':', '.'], ' ', $key)) => $value]; })->all(), EXTR_SKIP); ?>
{{ props }}
<x-{{ component }} {{ bindings }} {{ attributes }}>
{{ slots }}
{{ defaultSlot }}
</x-{{ component }}>
EOF;

return function ($data) use ($template) {
$bindings = $this->bindings($class = $this->classForComponent());

return str_replace(
[
'{{ component }}',
'{{ props }}',
'{{ bindings }}',
'{{ attributes }}',
'{{ slots }}',
'{{ defaultSlot }}',
],
[
$this->component,
$this->compileProps($bindings),
$this->compileBindings($bindings),
class_exists($class) ? '{{ $attributes }}' : '',
$this->compileSlots($data['__laravel_slots']),
'{{ $slot ?? "" }}',
],
$template
);
};
}







protected function compileProps(array $bindings)
{
if (empty($bindings)) {
return '';
}

return '@props('.'[\''.implode('\',\'', collect($bindings)->map(function ($dataKey) {
return Str::camel($dataKey);
})->all()).'\']'.')';
}







protected function compileBindings(array $bindings)
{
return collect($bindings)->map(function ($key) {
return ':'.$key.'="$'.Str::camel(str_replace([':', '.'], ' ', $key)).'"';
})->implode(' ');
}







protected function compileSlots(array $slots)
{
return collect($slots)->map(function ($slot, $name) {
return $name === '__default' ? null : '<x-slot name="'.$name.'" '.((string) $slot->attributes).'>{{ $'.$name.' }}</x-slot>';
})->filter()->implode(PHP_EOL);
}






protected function classForComponent()
{
if (isset(static::$componentClasses[$this->component])) {
return static::$componentClasses[$this->component];
}

return static::$componentClasses[$this->component] =
$this->compiler()->componentClass($this->component);
}







protected function bindings(string $class)
{
[$data, $attributes] = $this->compiler()->partitionDataAndAttributes($class, $this->attributes->getAttributes());

return array_keys($data->all());
}






protected function compiler()
{
if (! static::$compiler) {
static::$compiler = new ComponentTagCompiler(
Container::getInstance()->make('blade.compiler')->getClassComponentAliases(),
Container::getInstance()->make('blade.compiler')->getClassComponentNamespaces(),
Container::getInstance()->make('blade.compiler')
);
}

return static::$compiler;
}
}
