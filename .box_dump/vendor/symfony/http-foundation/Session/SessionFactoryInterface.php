<?php










namespace Symfony\Component\HttpFoundation\Session;




interface SessionFactoryInterface
{
public function createSession(): SessionInterface;
}
