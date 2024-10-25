<?php










namespace Symfony\Component\HttpKernel;

use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;






final class KernelEvents
{
/**
@Event("Symfony\Component\HttpKernel\Event\RequestEvent")






*/
public const REQUEST = 'kernel.request';

/**
@Event("Symfony\Component\HttpKernel\Event\ExceptionEvent")





*/
public const EXCEPTION = 'kernel.exception';

/**
@Event("Symfony\Component\HttpKernel\Event\ControllerEvent")






*/
public const CONTROLLER = 'kernel.controller';

/**
@Event("Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent")





*/
public const CONTROLLER_ARGUMENTS = 'kernel.controller_arguments';

/**
@Event("Symfony\Component\HttpKernel\Event\ViewEvent")






*/
public const VIEW = 'kernel.view';

/**
@Event("Symfony\Component\HttpKernel\Event\ResponseEvent")






*/
public const RESPONSE = 'kernel.response';

/**
@Event("Symfony\Component\HttpKernel\Event\FinishRequestEvent")





*/
public const FINISH_REQUEST = 'kernel.finish_request';

/**
@Event("Symfony\Component\HttpKernel\Event\TerminateEvent")




*/
public const TERMINATE = 'kernel.terminate';






public const ALIASES = [
ControllerArgumentsEvent::class => self::CONTROLLER_ARGUMENTS,
ControllerEvent::class => self::CONTROLLER,
ResponseEvent::class => self::RESPONSE,
FinishRequestEvent::class => self::FINISH_REQUEST,
RequestEvent::class => self::REQUEST,
ViewEvent::class => self::VIEW,
ExceptionEvent::class => self::EXCEPTION,
TerminateEvent::class => self::TERMINATE,
];
}
