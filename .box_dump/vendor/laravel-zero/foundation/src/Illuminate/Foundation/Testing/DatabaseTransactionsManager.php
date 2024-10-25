<?php

namespace Illuminate\Foundation\Testing;

use Illuminate\Database\DatabaseTransactionsManager as BaseManager;

class DatabaseTransactionsManager extends BaseManager
{






public function addCallback($callback)
{



if ($this->callbackApplicableTransactions()->count() === 0) {
return $callback();
}

$this->pendingTransactions->last()->addCallback($callback);
}






public function callbackApplicableTransactions()
{
return $this->pendingTransactions->skip(1)->values();
}







public function afterCommitCallbacksShouldBeExecuted($level)
{
return $level === 1;
}
}
