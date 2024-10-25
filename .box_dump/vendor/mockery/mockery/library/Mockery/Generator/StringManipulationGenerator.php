<?php









namespace Mockery\Generator;

use Mockery\Generator\StringManipulation\Pass\AvoidMethodClashPass;
use Mockery\Generator\StringManipulation\Pass\CallTypeHintPass;
use Mockery\Generator\StringManipulation\Pass\ClassAttributesPass;
use Mockery\Generator\StringManipulation\Pass\ClassNamePass;
use Mockery\Generator\StringManipulation\Pass\ClassPass;
use Mockery\Generator\StringManipulation\Pass\ConstantsPass;
use Mockery\Generator\StringManipulation\Pass\InstanceMockPass;
use Mockery\Generator\StringManipulation\Pass\InterfacePass;
use Mockery\Generator\StringManipulation\Pass\MagicMethodTypeHintsPass;
use Mockery\Generator\StringManipulation\Pass\MethodDefinitionPass;
use Mockery\Generator\StringManipulation\Pass\Pass;
use Mockery\Generator\StringManipulation\Pass\RemoveBuiltinMethodsThatAreFinalPass;
use Mockery\Generator\StringManipulation\Pass\RemoveDestructorPass;
use Mockery\Generator\StringManipulation\Pass\RemoveUnserializeForInternalSerializableClassesPass;
use Mockery\Generator\StringManipulation\Pass\TraitPass;
use function file_get_contents;

class StringManipulationGenerator implements Generator
{



protected $passes = [];




private $code;




public function __construct(array $passes)
{
$this->passes = $passes;

$this->code = file_get_contents(__DIR__ . '/../Mock.php');
}





public function addPass(Pass $pass)
{
$this->passes[] = $pass;
}




public function generate(MockConfiguration $config)
{
$className = $config->getName() ?: $config->generateName();

$namedConfig = $config->rename($className);

$code = $this->code;
foreach ($this->passes as $pass) {
$code = $pass->apply($code, $namedConfig);
}

return new MockDefinition($namedConfig, $code);
}






public static function withDefaultPasses()
{
return new static([
new CallTypeHintPass(),
new MagicMethodTypeHintsPass(),
new ClassPass(),
new TraitPass(),
new ClassNamePass(),
new InstanceMockPass(),
new InterfacePass(),
new AvoidMethodClashPass(),
new MethodDefinitionPass(),
new RemoveUnserializeForInternalSerializableClassesPass(),
new RemoveBuiltinMethodsThatAreFinalPass(),
new RemoveDestructorPass(),
new ConstantsPass(),
new ClassAttributesPass(),
]);
}
}
