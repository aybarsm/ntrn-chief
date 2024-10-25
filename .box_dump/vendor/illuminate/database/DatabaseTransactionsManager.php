<?php

namespace Illuminate\Database;

use Illuminate\Support\Collection;

class DatabaseTransactionsManager
{





protected $committedTransactions;






protected $pendingTransactions;






protected $currentTransaction = [];






public function __construct()
{
$this->committedTransactions = new Collection;
$this->pendingTransactions = new Collection;
}








public function begin($connection, $level)
{
$this->pendingTransactions->push(
$newTransaction = new DatabaseTransactionRecord(
$connection,
$level,
$this->currentTransaction[$connection] ?? null
)
);

$this->currentTransaction[$connection] = $newTransaction;
}









public function commit($connection, $levelBeingCommitted, $newTransactionLevel)
{
$this->stageTransactions($connection, $levelBeingCommitted);

if (isset($this->currentTransaction[$connection])) {
$this->currentTransaction[$connection] = $this->currentTransaction[$connection]->parent;
}

if (! $this->afterCommitCallbacksShouldBeExecuted($newTransactionLevel) &&
$newTransactionLevel !== 0) {
return [];
}




$this->pendingTransactions = $this->pendingTransactions->reject(
fn ($transaction) => $transaction->connection === $connection &&
$transaction->level >= $levelBeingCommitted
)->values();

[$forThisConnection, $forOtherConnections] = $this->committedTransactions->partition(
fn ($transaction) => $transaction->connection == $connection
);

$this->committedTransactions = $forOtherConnections->values();

$forThisConnection->map->executeCallbacks();

return $forThisConnection;
}








public function stageTransactions($connection, $levelBeingCommitted)
{
$this->committedTransactions = $this->committedTransactions->merge(
$this->pendingTransactions->filter(
fn ($transaction) => $transaction->connection === $connection &&
$transaction->level >= $levelBeingCommitted
)
);

$this->pendingTransactions = $this->pendingTransactions->reject(
fn ($transaction) => $transaction->connection === $connection &&
$transaction->level >= $levelBeingCommitted
);
}








public function rollback($connection, $newTransactionLevel)
{
if ($newTransactionLevel === 0) {
$this->removeAllTransactionsForConnection($connection);
} else {
$this->pendingTransactions = $this->pendingTransactions->reject(
fn ($transaction) => $transaction->connection == $connection &&
$transaction->level > $newTransactionLevel
)->values();

if ($this->currentTransaction) {
do {
$this->removeCommittedTransactionsThatAreChildrenOf($this->currentTransaction[$connection]);

$this->currentTransaction[$connection] = $this->currentTransaction[$connection]->parent;
} while (
isset($this->currentTransaction[$connection]) &&
$this->currentTransaction[$connection]->level > $newTransactionLevel
);
}
}
}







protected function removeAllTransactionsForConnection($connection)
{
$this->currentTransaction[$connection] = null;

$this->pendingTransactions = $this->pendingTransactions->reject(
fn ($transaction) => $transaction->connection == $connection
)->values();

$this->committedTransactions = $this->committedTransactions->reject(
fn ($transaction) => $transaction->connection == $connection
)->values();
}







protected function removeCommittedTransactionsThatAreChildrenOf(DatabaseTransactionRecord $transaction)
{
[$removedTransactions, $this->committedTransactions] = $this->committedTransactions->partition(
fn ($committed) => $committed->connection == $transaction->connection &&
$committed->parent === $transaction
);




$removedTransactions->each(
fn ($transaction) => $this->removeCommittedTransactionsThatAreChildrenOf($transaction)
);
}







public function addCallback($callback)
{
if ($current = $this->callbackApplicableTransactions()->last()) {
return $current->addCallback($callback);
}

$callback();
}






public function callbackApplicableTransactions()
{
return $this->pendingTransactions;
}







public function afterCommitCallbacksShouldBeExecuted($level)
{
return $level === 0;
}






public function getPendingTransactions()
{
return $this->pendingTransactions;
}






public function getCommittedTransactions()
{
return $this->committedTransactions;
}
}
