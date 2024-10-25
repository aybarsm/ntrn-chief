<?php declare(strict_types=1);










namespace Monolog\Handler;

use Monolog\Processor\ProcessorInterface;
use Monolog\LogRecord;






interface ProcessableHandlerInterface
{
/**
@phpstan-param





*/
public function pushProcessor(callable $callback): HandlerInterface;

/**
@phpstan-return





*/
public function popProcessor(): callable;
}
