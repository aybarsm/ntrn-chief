<?php










namespace Symfony\Component\HttpKernel\Fragment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;






interface FragmentRendererInterface
{



public function render(string|ControllerReference $uri, Request $request, array $options = []): Response;




public function getName(): string;
}
