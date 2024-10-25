<?php

namespace Illuminate\Console\Scheduling;

use Illuminate\Console\Command;
use Illuminate\Console\Events\ScheduledBackgroundTaskFinished;
use Illuminate\Contracts\Events\Dispatcher;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'schedule:finish')]
class ScheduleFinishCommand extends Command
{





protected $signature = 'schedule:finish {id} {code=0}';






protected $description = 'Handle the completion of a scheduled command';






protected $hidden = true;







public function handle(Schedule $schedule)
{
collect($schedule->events())->filter(function ($value) {
return $value->mutexName() == $this->argument('id');
})->each(function ($event) {
$event->finish($this->laravel, $this->argument('code'));

$this->laravel->make(Dispatcher::class)->dispatch(new ScheduledBackgroundTaskFinished($event));
});
}
}
