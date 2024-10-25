<?php

namespace Illuminate\Database\Connectors;

use Illuminate\Database\SQLiteDatabaseDoesNotExistException;

class SQLiteConnector extends Connector implements ConnectorInterface
{








public function connect(array $config)
{
$options = $this->getOptions($config);




if ($config['database'] === ':memory:') {
return $this->createConnection('sqlite::memory:', $config, $options);
}

$path = realpath($config['database']);




if ($path === false) {
throw new SQLiteDatabaseDoesNotExistException($config['database']);
}

return $this->createConnection("sqlite:{$path}", $config, $options);
}
}
