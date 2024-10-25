<?php

namespace Illuminate\Database\Eloquent;

trait BroadcastsEventsAfterCommit
{
use BroadcastsEvents;






public function broadcastAfterCommit()
{
return true;
}
}
