<?php

namespace Illuminate\Session;

use Illuminate\Support\Manager;

/**
@mixin
*/
class SessionManager extends Manager
{






protected function callCustomCreator($driver)
{
return $this->buildSession(parent::callCustomCreator($driver));
}






protected function createNullDriver()
{
return $this->buildSession(new NullSessionHandler);
}






protected function createArrayDriver()
{
return $this->buildSession(new ArraySessionHandler(
$this->config->get('session.lifetime')
));
}






protected function createCookieDriver()
{
return $this->buildSession(new CookieSessionHandler(
$this->container->make('cookie'),
$this->config->get('session.lifetime'),
$this->config->get('session.expire_on_close')
));
}






protected function createFileDriver()
{
return $this->createNativeDriver();
}






protected function createNativeDriver()
{
$lifetime = $this->config->get('session.lifetime');

return $this->buildSession(new FileSessionHandler(
$this->container->make('files'), $this->config->get('session.files'), $lifetime
));
}






protected function createDatabaseDriver()
{
$table = $this->config->get('session.table');

$lifetime = $this->config->get('session.lifetime');

return $this->buildSession(new DatabaseSessionHandler(
$this->getDatabaseConnection(), $table, $lifetime, $this->container
));
}






protected function getDatabaseConnection()
{
$connection = $this->config->get('session.connection');

return $this->container->make('db')->connection($connection);
}






protected function createApcDriver()
{
return $this->createCacheBased('apc');
}






protected function createMemcachedDriver()
{
return $this->createCacheBased('memcached');
}






protected function createRedisDriver()
{
$handler = $this->createCacheHandler('redis');

$handler->getCache()->getStore()->setConnection(
$this->config->get('session.connection')
);

return $this->buildSession($handler);
}






protected function createDynamodbDriver()
{
return $this->createCacheBased('dynamodb');
}







protected function createCacheBased($driver)
{
return $this->buildSession($this->createCacheHandler($driver));
}







protected function createCacheHandler($driver)
{
$store = $this->config->get('session.store') ?: $driver;

return new CacheBasedSessionHandler(
clone $this->container->make('cache')->store($store),
$this->config->get('session.lifetime')
);
}







protected function buildSession($handler)
{
return $this->config->get('session.encrypt')
? $this->buildEncryptedSession($handler)
: new Store(
$this->config->get('session.cookie'),
$handler,
$id = null,
$this->config->get('session.serialization', 'php')
);
}







protected function buildEncryptedSession($handler)
{
return new EncryptedStore(
$this->config->get('session.cookie'),
$handler,
$this->container['encrypter'],
$id = null,
$this->config->get('session.serialization', 'php'),
);
}






public function shouldBlock()
{
return $this->config->get('session.block', false);
}






public function blockDriver()
{
return $this->config->get('session.block_store');
}






public function defaultRouteBlockLockSeconds()
{
return $this->config->get('session.block_lock_seconds', 10);
}






public function defaultRouteBlockWaitSeconds()
{
return $this->config->get('session.block_wait_seconds', 10);
}






public function getSessionConfig()
{
return $this->config->get('session');
}






public function getDefaultDriver()
{
return $this->config->get('session.driver');
}







public function setDefaultDriver($name)
{
$this->config->set('session.driver', $name);
}
}
