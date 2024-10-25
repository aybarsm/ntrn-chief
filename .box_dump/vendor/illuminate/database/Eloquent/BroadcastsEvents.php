<?php

namespace Illuminate\Database\Eloquent;

use Illuminate\Support\Arr;

trait BroadcastsEvents
{





public static function bootBroadcastsEvents()
{
static::created(function ($model) {
$model->broadcastCreated();
});

static::updated(function ($model) {
$model->broadcastUpdated();
});

if (method_exists(static::class, 'bootSoftDeletes')) {
static::softDeleted(function ($model) {
$model->broadcastTrashed();
});

static::restored(function ($model) {
$model->broadcastRestored();
});
}

static::deleted(function ($model) {
$model->broadcastDeleted();
});
}







public function broadcastCreated($channels = null)
{
return $this->broadcastIfBroadcastChannelsExistForEvent(
$this->newBroadcastableModelEvent('created'), 'created', $channels
);
}







public function broadcastUpdated($channels = null)
{
return $this->broadcastIfBroadcastChannelsExistForEvent(
$this->newBroadcastableModelEvent('updated'), 'updated', $channels
);
}







public function broadcastTrashed($channels = null)
{
return $this->broadcastIfBroadcastChannelsExistForEvent(
$this->newBroadcastableModelEvent('trashed'), 'trashed', $channels
);
}







public function broadcastRestored($channels = null)
{
return $this->broadcastIfBroadcastChannelsExistForEvent(
$this->newBroadcastableModelEvent('restored'), 'restored', $channels
);
}







public function broadcastDeleted($channels = null)
{
return $this->broadcastIfBroadcastChannelsExistForEvent(
$this->newBroadcastableModelEvent('deleted'), 'deleted', $channels
);
}









protected function broadcastIfBroadcastChannelsExistForEvent($instance, $event, $channels = null)
{
if (! static::$isBroadcasting) {
return;
}

if (! empty($this->broadcastOn($event)) || ! empty($channels)) {
return broadcast($instance->onChannels(Arr::wrap($channels)));
}
}







public function newBroadcastableModelEvent($event)
{
return tap($this->newBroadcastableEvent($event), function ($event) {
$event->connection = property_exists($this, 'broadcastConnection')
? $this->broadcastConnection
: $this->broadcastConnection();

$event->queue = property_exists($this, 'broadcastQueue')
? $this->broadcastQueue
: $this->broadcastQueue();

$event->afterCommit = property_exists($this, 'broadcastAfterCommit')
? $this->broadcastAfterCommit
: $this->broadcastAfterCommit();
});
}







protected function newBroadcastableEvent(string $event)
{
return new BroadcastableModelEventOccurred($this, $event);
}







public function broadcastOn($event)
{
return [$this];
}






public function broadcastConnection()
{

}






public function broadcastQueue()
{

}






public function broadcastAfterCommit()
{
return false;
}
}
