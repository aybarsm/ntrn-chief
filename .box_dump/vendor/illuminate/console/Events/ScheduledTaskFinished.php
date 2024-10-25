<?php

namespace Illuminate\Console\Events;

use Illuminate\Console\Scheduling\Event;

class ScheduledTaskFinished
{





public $task;






public $runtime;








public function __construct(Event $task, $runtime)
{
$this->task = $task;
$this->runtime = $runtime;
}
}
