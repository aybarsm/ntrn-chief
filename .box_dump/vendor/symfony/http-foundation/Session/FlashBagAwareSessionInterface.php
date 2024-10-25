<?php










namespace Symfony\Component\HttpFoundation\Session;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;




interface FlashBagAwareSessionInterface extends SessionInterface
{
public function getFlashBag(): FlashBagInterface;
}
