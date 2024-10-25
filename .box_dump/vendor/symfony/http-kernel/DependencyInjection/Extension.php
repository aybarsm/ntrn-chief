<?php










namespace Symfony\Component\HttpKernel\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension as BaseExtension;








abstract class Extension extends BaseExtension
{
private array $annotatedClasses = [];








public function getAnnotatedClassesToCompile(): array
{
trigger_deprecation('symfony/http-kernel', '7.1', 'The "%s()" method is deprecated since Symfony 7.1 and will be removed in 8.0.', __METHOD__);

return $this->annotatedClasses;
}








public function addAnnotatedClassesToCompile(array $annotatedClasses): void
{
trigger_deprecation('symfony/http-kernel', '7.1', 'The "%s()" method is deprecated since Symfony 7.1 and will be removed in 8.0.', __METHOD__);

$this->annotatedClasses = array_merge($this->annotatedClasses, $annotatedClasses);
}
}
