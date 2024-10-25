<?php










namespace Symfony\Component\Console\Formatter;

use Symfony\Component\Console\Color;






class OutputFormatterStyle implements OutputFormatterStyleInterface
{
private Color $color;
private string $foreground;
private string $background;
private array $options;
private ?string $href = null;
private bool $handlesHrefGracefully;







public function __construct(?string $foreground = null, ?string $background = null, array $options = [])
{
$this->color = new Color($this->foreground = $foreground ?: '', $this->background = $background ?: '', $this->options = $options);
}

public function setForeground(?string $color): void
{
$this->color = new Color($this->foreground = $color ?: '', $this->background, $this->options);
}

public function setBackground(?string $color): void
{
$this->color = new Color($this->foreground, $this->background = $color ?: '', $this->options);
}

public function setHref(string $url): void
{
$this->href = $url;
}

public function setOption(string $option): void
{
$this->options[] = $option;
$this->color = new Color($this->foreground, $this->background, $this->options);
}

public function unsetOption(string $option): void
{
$pos = array_search($option, $this->options);
if (false !== $pos) {
unset($this->options[$pos]);
}

$this->color = new Color($this->foreground, $this->background, $this->options);
}

public function setOptions(array $options): void
{
$this->color = new Color($this->foreground, $this->background, $this->options = $options);
}

public function apply(string $text): string
{
$this->handlesHrefGracefully ??= 'JetBrains-JediTerm' !== getenv('TERMINAL_EMULATOR')
&& (!getenv('KONSOLE_VERSION') || (int) getenv('KONSOLE_VERSION') > 201100)
&& !isset($_SERVER['IDEA_INITIAL_DIRECTORY']);

if (null !== $this->href && $this->handlesHrefGracefully) {
$text = "\033]8;;$this->href\033\\$text\033]8;;\033\\";
}

return $this->color->apply($text);
}
}