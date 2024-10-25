<?php










namespace Symfony\Component\HttpKernel\Fragment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Twig\Environment;






class HIncludeFragmentRenderer extends RoutableFragmentRenderer
{



public function __construct(
private ?Environment $twig = null,
private ?UriSigner $signer = null,
private ?string $globalDefaultTemplate = null,
private string $charset = 'utf-8',
) {
}




public function hasTemplating(): bool
{
return null !== $this->twig;
}








public function render(string|ControllerReference $uri, Request $request, array $options = []): Response
{
if ($uri instanceof ControllerReference) {
$uri = (new FragmentUriGenerator($this->fragmentPath, $this->signer))->generate($uri, $request);
}


$uri = str_replace('&', '&amp;', $uri);

$template = $options['default'] ?? $this->globalDefaultTemplate;
if (null !== $this->twig && $template && $this->twig->getLoader()->exists($template)) {
$content = $this->twig->render($template);
} else {
$content = $template;
}

$attributes = isset($options['attributes']) && \is_array($options['attributes']) ? $options['attributes'] : [];
if (isset($options['id']) && $options['id']) {
$attributes['id'] = $options['id'];
}
$renderedAttributes = '';
if (\count($attributes) > 0) {
$flags = \ENT_QUOTES | \ENT_SUBSTITUTE;
foreach ($attributes as $attribute => $value) {
$renderedAttributes .= sprintf(
' %s="%s"',
htmlspecialchars($attribute, $flags, $this->charset, false),
htmlspecialchars($value, $flags, $this->charset, false)
);
}
}

return new Response(sprintf('<hx:include src="%s"%s>%s</hx:include>', $uri, $renderedAttributes, $content));
}

public function getName(): string
{
return 'hinclude';
}
}
