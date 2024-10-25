<?php










namespace Symfony\Component\Mime\HtmlToTextConverter;

use League\HTMLToMarkdown\HtmlConverter;
use League\HTMLToMarkdown\HtmlConverterInterface;




class LeagueHtmlToMarkdownConverter implements HtmlToTextConverterInterface
{
public function __construct(
private HtmlConverterInterface $converter = new HtmlConverter([
'hard_break' => true,
'strip_tags' => true,
'remove_nodes' => 'head style',
]),
) {
}

public function convert(string $html, string $charset): string
{
return $this->converter->convert($html);
}
}
