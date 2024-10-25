<?php










namespace Symfony\Component\HttpKernel\Controller;

use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;







class ErrorController
{
public function __construct(
private HttpKernelInterface $kernel,
private string|object|array|null $controller,
private ErrorRendererInterface $errorRenderer,
) {
}

public function __invoke(\Throwable $exception): Response
{
$exception = $this->errorRenderer->render($exception);

return new Response($exception->getAsString(), $exception->getStatusCode(), $exception->getHeaders());
}

public function preview(Request $request, int $code): Response
{





$subRequest = $request->duplicate(null, null, [
'_controller' => $this->controller,
'exception' => new HttpException($code, 'This is a sample exception.'),
'logger' => null,
'showException' => false,
]);

return $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
}
}
