<?php










namespace Symfony\Component\HttpKernel\DependencyInjection;

use Composer\Autoload\ClassLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\ErrorHandler\DebugClassLoader;
use Symfony\Component\HttpKernel\Kernel;

trigger_deprecation('symfony/http-kernel', '7.1', 'The "%s" class is deprecated since Symfony 7.1 and will be removed in 8.0.', AddAnnotatedClassesToCachePass::class);








class AddAnnotatedClassesToCachePass implements CompilerPassInterface
{
public function __construct(
private Kernel $kernel,
) {
}

public function process(ContainerBuilder $container): void
{
$annotatedClasses = [];
foreach ($container->getExtensions() as $extension) {
if ($extension instanceof Extension) {
$annotatedClasses[] = $extension->getAnnotatedClassesToCompile();
}
}

$annotatedClasses = array_merge($this->kernel->getAnnotatedClassesToCompile(), ...$annotatedClasses);

$existingClasses = $this->getClassesInComposerClassMaps();

$annotatedClasses = $container->getParameterBag()->resolveValue($annotatedClasses);
$this->kernel->setAnnotatedClassCache($this->expandClasses($annotatedClasses, $existingClasses));
}







private function expandClasses(array $patterns, array $classes): array
{
$expanded = [];


foreach ($patterns as $key => $pattern) {
if (!str_ends_with($pattern, '\\') && !str_contains($pattern, '*')) {
unset($patterns[$key]);
$expanded[] = ltrim($pattern, '\\');
}
}


$regexps = $this->patternsToRegexps($patterns);

foreach ($classes as $class) {
$class = ltrim($class, '\\');

if ($this->matchAnyRegexps($class, $regexps)) {
$expanded[] = $class;
}
}

return array_unique($expanded);
}

private function getClassesInComposerClassMaps(): array
{
$classes = [];

foreach (spl_autoload_functions() as $function) {
if (!\is_array($function)) {
continue;
}

if ($function[0] instanceof DebugClassLoader) {
$function = $function[0]->getClassLoader();
}

if (\is_array($function) && $function[0] instanceof ClassLoader) {
$classes += array_filter($function[0]->getClassMap());
}
}

return array_keys($classes);
}

private function patternsToRegexps(array $patterns): array
{
$regexps = [];

foreach ($patterns as $pattern) {

$regex = preg_quote(ltrim($pattern, '\\'));


$regex = strtr($regex, ['\\*\\*' => '.*?', '\\*' => '[^\\\\]*?']);


if (!str_ends_with($regex, '\\')) {
$regex .= '$';
}

$regexps[] = '{^\\\\'.$regex.'}';
}

return $regexps;
}

private function matchAnyRegexps(string $class, array $regexps): bool
{
$isTest = str_contains($class, 'Test');

foreach ($regexps as $regex) {
if ($isTest && !str_contains($regex, 'Test')) {
continue;
}

if (preg_match($regex, '\\'.$class)) {
return true;
}
}

return false;
}
}
