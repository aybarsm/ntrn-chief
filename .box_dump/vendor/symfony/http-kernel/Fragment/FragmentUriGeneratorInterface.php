<?php










namespace Symfony\Component\HttpKernel\Fragment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;






interface FragmentUriGeneratorInterface
{







public function generate(ControllerReference $controller, ?Request $request = null, bool $absolute = false, bool $strict = true, bool $sign = true): string;
}
