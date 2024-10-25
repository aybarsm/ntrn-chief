<?php










namespace Symfony\Component\HttpKernel\Controller;

use Symfony\Component\HttpFoundation\Request;









interface ControllerResolverInterface
{














public function getController(Request $request): callable|false;
}
