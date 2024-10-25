<?php










namespace Symfony\Component\HttpKernel\Fragment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\HttpCache\SurrogateInterface;






abstract class AbstractSurrogateFragmentRenderer extends RoutableFragmentRenderer
{






public function __construct(
private ?SurrogateInterface $surrogate,
private FragmentRendererInterface $inlineStrategy,
private ?UriSigner $signer = null,
) {
}
















public function render(string|ControllerReference $uri, Request $request, array $options = []): Response
{
if (!$this->surrogate || !$this->surrogate->hasSurrogateCapability($request)) {
$request->attributes->set('_check_controller_is_allowed', true);

if ($uri instanceof ControllerReference && $this->containsNonScalars($uri->attributes)) {
throw new \InvalidArgumentException('Passing non-scalar values as part of URI attributes to the ESI and SSI rendering strategies is not supported. Use a different rendering strategy or pass scalar values.');
}

return $this->inlineStrategy->render($uri, $request, $options);
}

$absolute = $options['absolute_uri'] ?? false;

if ($uri instanceof ControllerReference) {
$uri = $this->generateSignedFragmentUri($uri, $request, $absolute);
}

$alt = $options['alt'] ?? null;
if ($alt instanceof ControllerReference) {
$alt = $this->generateSignedFragmentUri($alt, $request, $absolute);
}

$tag = $this->surrogate->renderIncludeTag($uri, $alt, $options['ignore_errors'] ?? false, $options['comment'] ?? '');

return new Response($tag);
}

private function generateSignedFragmentUri(ControllerReference $uri, Request $request, bool $absolute): string
{
return (new FragmentUriGenerator($this->fragmentPath, $this->signer))->generate($uri, $request, $absolute);
}

private function containsNonScalars(array $values): bool
{
foreach ($values as $value) {
if (\is_scalar($value) || null === $value) {
continue;
}

if (!\is_array($value) || $this->containsNonScalars($value)) {
return true;
}
}

return false;
}
}
