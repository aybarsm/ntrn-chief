<?php










namespace Symfony\Component\HttpKernel\Fragment;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Exception\HttpException;











class FragmentHandler
{

private array $renderers = [];





public function __construct(
private RequestStack $requestStack,
array $renderers = [],
private bool $debug = false,
) {
foreach ($renderers as $renderer) {
$this->addRenderer($renderer);
}
}




public function addRenderer(FragmentRendererInterface $renderer): void
{
$this->renderers[$renderer->getName()] = $renderer;
}











public function render(string|ControllerReference $uri, string $renderer = 'inline', array $options = []): ?string
{
if (!isset($options['ignore_errors'])) {
$options['ignore_errors'] = !$this->debug;
}

if (!isset($this->renderers[$renderer])) {
throw new \InvalidArgumentException(sprintf('The "%s" renderer does not exist.', $renderer));
}

if (!$request = $this->requestStack->getCurrentRequest()) {
throw new \LogicException('Rendering a fragment can only be done when handling a Request.');
}

return $this->deliver($this->renderers[$renderer]->render($uri, $request, $options));
}











protected function deliver(Response $response): ?string
{
if (!$response->isSuccessful()) {
$responseStatusCode = $response->getStatusCode();
throw new \RuntimeException(sprintf('Error when rendering "%s" (Status code is %d).', $this->requestStack->getCurrentRequest()->getUri(), $responseStatusCode), 0, new HttpException($responseStatusCode));
}

if (!$response instanceof StreamedResponse) {
return $response->getContent();
}

$response->sendContent();

return null;
}
}
