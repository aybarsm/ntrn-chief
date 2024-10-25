<?php

namespace Illuminate\Database\Eloquent;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class BroadcastableModelEventOccurred implements ShouldBroadcast
{
use InteractsWithSockets, SerializesModels;






public $model;






protected $event;






protected $channels = [];






public $connection;






public $queue;






public $afterCommit;








public function __construct($model, $event)
{
$this->model = $model;
$this->event = $event;
}






public function broadcastOn()
{
$channels = empty($this->channels)
? ($this->model->broadcastOn($this->event) ?: [])
: $this->channels;

return collect($channels)->map(function ($channel) {
return $channel instanceof Model ? new PrivateChannel($channel) : $channel;
})->all();
}






public function broadcastAs()
{
$default = class_basename($this->model).ucfirst($this->event);

return method_exists($this->model, 'broadcastAs')
? ($this->model->broadcastAs($this->event) ?: $default)
: $default;
}






public function broadcastWith()
{
return method_exists($this->model, 'broadcastWith')
? $this->model->broadcastWith($this->event)
: null;
}







public function onChannels(array $channels)
{
$this->channels = $channels;

return $this;
}






public function shouldBroadcastNow()
{
return $this->event === 'deleted' &&
! method_exists($this->model, 'bootSoftDeletes');
}






public function event()
{
return $this->event;
}
}
