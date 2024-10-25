<?php










namespace Symfony\Component\Mime;




interface BodyRendererInterface
{
public function render(Message $message): void;
}
