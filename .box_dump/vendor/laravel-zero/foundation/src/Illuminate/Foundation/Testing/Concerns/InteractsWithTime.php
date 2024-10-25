<?php

namespace Illuminate\Foundation\Testing\Concerns;

use Illuminate\Foundation\Testing\Wormhole;
use Illuminate\Support\Carbon;

trait InteractsWithTime
{






public function freezeTime($callback = null)
{
return $this->travelTo(Carbon::now(), $callback);
}







public function freezeSecond($callback = null)
{
return $this->travelTo(Carbon::now()->startOfSecond(), $callback);
}







public function travel($value)
{
return new Wormhole($value);
}








public function travelTo($date, $callback = null)
{
Carbon::setTestNow($date);

if ($callback) {
return tap($callback($date), function () {
Carbon::setTestNow();
});
}
}






public function travelBack()
{
return Wormhole::back();
}
}
