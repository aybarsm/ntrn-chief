<?php










namespace Symfony\Component\Console\Command;

use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\Store\SemaphoreStore;






trait LockableTrait
{
private ?LockInterface $lock = null;

private ?LockFactory $lockFactory = null;




private function lock(?string $name = null, bool $blocking = false): bool
{
if (!class_exists(SemaphoreStore::class)) {
throw new LogicException('To enable the locking feature you must install the symfony/lock component. Try running "composer require symfony/lock".');
}

if (null !== $this->lock) {
throw new LogicException('A lock is already in place.');
}

if (null === $this->lockFactory) {
if (SemaphoreStore::isSupported()) {
$store = new SemaphoreStore();
} else {
$store = new FlockStore();
}

$this->lockFactory = (new LockFactory($store));
}

$this->lock = $this->lockFactory->createLock($name ?: $this->getName());
if (!$this->lock->acquire($blocking)) {
$this->lock = null;

return false;
}

return true;
}




private function release(): void
{
if ($this->lock) {
$this->lock->release();
$this->lock = null;
}
}
}
