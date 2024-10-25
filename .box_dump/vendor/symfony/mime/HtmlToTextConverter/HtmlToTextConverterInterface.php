<?php










namespace Symfony\Component\Mime\HtmlToTextConverter;




interface HtmlToTextConverterInterface
{





public function convert(string $html, string $charset): string;
}
