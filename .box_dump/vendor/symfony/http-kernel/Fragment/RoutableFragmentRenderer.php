<?php










namespace Symfony\Component\HttpKernel\Fragment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\EventListener\FragmentListener;






abstract class RoutableFragmentRenderer implements FragmentRendererInterface
{



protected string $fragmentPath = '/_fragment';






public function setFragmentPath(string $path): void
{
$this->fragmentPath = $path;
}







protected function generateFragmentUri(ControllerReference $reference, Request $request, bool $absolute = false, bool $strict = true): string
{
return (new FragmentUriGenerator($this->fragmentPath))->generate($reference, $request, $absolute, $strict, false);
}
}
