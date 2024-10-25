<?php










namespace Symfony\Component\Mime\HtmlToTextConverter;




class DefaultHtmlToTextConverter implements HtmlToTextConverterInterface
{
public function convert(string $html, string $charset): string
{
return strip_tags(preg_replace('{<(head|style)\b.*?</\1>}is', '', $html));
}
}
