<?php

namespace Illuminate\Database\Concerns;

use Closure;
use Illuminate\Database\DeadlockException;
use RuntimeException;
use Throwable;

trait ManagesTransactions
{









public function transaction(Closure $callback, $attempts = 1)
{
for ($currentAttempt = 1; $currentAttempt <= $attempts; $currentAttempt++) {
$this->beginTransaction();




try {
$callbackResult = $callback($this);
}




catch (Throwable $e) {
$this->handleTransactionException(
$e, $currentAttempt, $attempts
);

continue;
}

$levelBeingCommitted = $this->transactions;

try {
if ($this->transactions == 1) {
$this->fireConnectionEvent('committing');
$this->getPdo()->commit();
}

$this->transactions = max(0, $this->transactions - 1);
} catch (Throwable $e) {
$this->handleCommitTransactionException(
$e, $currentAttempt, $attempts
);

continue;
}

$this->transactionsManager?->commit(
$this->getName(),
$levelBeingCommitted,
$this->transactions
);

$this->fireConnectionEvent('committed');

return $callbackResult;
}
}











protected function handleTransactionException(Throwable $e, $currentAttempt, $maxAttempts)
{



if ($this->causedByConcurrencyError($e) &&
$this->transactions > 1) {
$this->transactions--;

$this->transactionsManager?->rollback(
$this->getName(), $this->transactions
);

throw new DeadlockException($e->getMessage(), is_int($e->getCode()) ? $e->getCode() : 0, $e);
}




$this->rollBack();

if ($this->causedByConcurrencyError($e) &&
$currentAttempt < $maxAttempts) {
return;
}

throw $e;
}








public function beginTransaction()
{
foreach ($this->beforeStartingTransaction as $callback) {
$callback($this);
}

$this->createTransaction();

$this->transactions++;

$this->transactionsManager?->begin(
$this->getName(), $this->transactions
);

$this->fireConnectionEvent('beganTransaction');
}








protected function createTransaction()
{
if ($this->transactions == 0) {
$this->reconnectIfMissingConnection();

try {
$this->getPdo()->beginTransaction();
} catch (Throwable $e) {
$this->handleBeginTransactionException($e);
}
} elseif ($this->transactions >= 1 && $this->queryGrammar->supportsSavepoints()) {
$this->createSavepoint();
}
}








protected function createSavepoint()
{
$this->getPdo()->exec(
$this->queryGrammar->compileSavepoint('trans'.($this->transactions + 1))
);
}









protected function handleBeginTransactionException(Throwable $e)
{
if ($this->causedByLostConnection($e)) {
$this->reconnect();

$this->getPdo()->beginTransaction();
} else {
throw $e;
}
}








public function commit()
{
if ($this->transactionLevel() == 1) {
$this->fireConnectionEvent('committing');
$this->getPdo()->commit();
}

[$levelBeingCommitted, $this->transactions] = [
$this->transactions,
max(0, $this->transactions - 1),
];

$this->transactionsManager?->commit(
$this->getName(), $levelBeingCommitted, $this->transactions
);

$this->fireConnectionEvent('committed');
}











protected function handleCommitTransactionException(Throwable $e, $currentAttempt, $maxAttempts)
{
$this->transactions = max(0, $this->transactions - 1);

if ($this->causedByConcurrencyError($e) && $currentAttempt < $maxAttempts) {
return;
}

if ($this->causedByLostConnection($e)) {
$this->transactions = 0;
}

throw $e;
}









public function rollBack($toLevel = null)
{



$toLevel = is_null($toLevel)
? $this->transactions - 1
: $toLevel;

if ($toLevel < 0 || $toLevel >= $this->transactions) {
return;
}




try {
$this->performRollBack($toLevel);
} catch (Throwable $e) {
$this->handleRollBackException($e);
}

$this->transactions = $toLevel;

$this->transactionsManager?->rollback(
$this->getName(), $this->transactions
);

$this->fireConnectionEvent('rollingBack');
}









protected function performRollBack($toLevel)
{
if ($toLevel == 0) {
$pdo = $this->getPdo();

if ($pdo->inTransaction()) {
$pdo->rollBack();
}
} elseif ($this->queryGrammar->supportsSavepoints()) {
$this->getPdo()->exec(
$this->queryGrammar->compileSavepointRollBack('trans'.($toLevel + 1))
);
}
}









protected function handleRollBackException(Throwable $e)
{
if ($this->causedByLostConnection($e)) {
$this->transactions = 0;

$this->transactionsManager?->rollback(
$this->getName(), $this->transactions
);
}

throw $e;
}






public function transactionLevel()
{
return $this->transactions;
}









public function afterCommit($callback)
{
if ($this->transactionsManager) {
return $this->transactionsManager->addCallback($callback);
}

throw new RuntimeException('Transactions Manager has not been set.');
}
}
