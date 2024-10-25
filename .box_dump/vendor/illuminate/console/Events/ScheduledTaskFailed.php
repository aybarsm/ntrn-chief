<?php

namespace Illuminate\Console\Events;

use Illuminate\Console\Scheduling\Event;
use Throwable;

class ScheduledTaskFailed
{





public $task;






public $exception;








public function __construct(Event $task, Throwable $exception)
{
$this->task = $task;
$this->exception = $exception;
}
}
