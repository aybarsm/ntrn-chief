<?php










namespace Symfony\Component\HttpKernel\HttpCache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;












class Esi extends AbstractSurrogate
{
public function getName(): string
{
return 'esi';
}

public function addSurrogateControl(Response $response): void
{
if (str_contains($response->getContent(), '<esi:include')) {
$response->headers->set('Surrogate-Control', 'content="ESI/1.0"');
}
}

public function renderIncludeTag(string $uri, ?string $alt = null, bool $ignoreErrors = true, string $comment = ''): string
{
$html = sprintf('<esi:include src="%s"%s%s />',
$uri,
$ignoreErrors ? ' onerror="continue"' : '',
$alt ? sprintf(' alt="%s"', $alt) : ''
);

if ($comment) {
return sprintf("<esi:comment text=\"%s\" />\n%s", $comment, $html);
}

return $html;
}

public function process(Request $request, Response $response): Response
{
$type = $response->headers->get('Content-Type');
if (!$type) {
$type = 'text/html';
}

$parts = explode(';', $type);
if (!\in_array($parts[0], $this->contentTypes, true)) {
return $response;
}


$content = $response->getContent();
$content = preg_replace('#<esi\:remove>.*?</esi\:remove>#s', '', $content);
$content = preg_replace('#<esi\:comment[^>]+>#s', '', $content);

$boundary = self::generateBodyEvalBoundary();
$chunks = preg_split('#<esi\:include\s+(.*?)\s*(?:/|</esi\:include)>#', $content, -1, \PREG_SPLIT_DELIM_CAPTURE);

$i = 1;
while (isset($chunks[$i])) {
$options = [];
preg_match_all('/(src|onerror|alt)="([^"]*?)"/', $chunks[$i], $matches, \PREG_SET_ORDER);
foreach ($matches as $set) {
$options[$set[1]] = $set[2];
}

if (!isset($options['src'])) {
throw new \RuntimeException('Unable to process an ESI tag without a "src" attribute.');
}

$chunks[$i] = $boundary.$options['src']."\n".($options['alt'] ?? '')."\n".('continue' === ($options['onerror'] ?? ''))."\n";
$i += 2;
}
$content = $boundary.implode('', $chunks).$boundary;

$response->setContent($content);
$response->headers->set('X-Body-Eval', 'ESI');


$this->removeFromControl($response);

return $response;
}
}
