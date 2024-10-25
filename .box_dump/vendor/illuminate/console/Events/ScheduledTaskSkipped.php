<?php

namespace Illuminate\Console\Events;

use Illuminate\Console\Scheduling\Event;

class ScheduledTaskSkipped
{





public $task;







public function __construct(Event $task)
{
$this->task = $task;
}
}
