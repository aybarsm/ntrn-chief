<?php

namespace Illuminate\Database\Console;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Events\DatabaseBusy;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'db:monitor')]
class MonitorCommand extends DatabaseInspectionCommand
{





protected $signature = 'db:monitor
                {--databases= : The database connections to monitor}
                {--max= : The maximum number of connections that can be open before an event is dispatched}';






protected $description = 'Monitor the number of connections on the specified database';






protected $connection;






protected $events;







public function __construct(ConnectionResolverInterface $connection, Dispatcher $events)
{
parent::__construct();

$this->connection = $connection;
$this->events = $events;
}






public function handle()
{
$databases = $this->parseDatabases($this->option('databases'));

$this->displayConnections($databases);

if ($this->option('max')) {
$this->dispatchEvents($databases);
}
}







protected function parseDatabases($databases)
{
return collect(explode(',', $databases))->map(function ($database) {
if (! $database) {
$database = $this->laravel['config']['database.default'];
}

$maxConnections = $this->option('max');

$connections = $this->connection->connection($database)->threadCount();

return [
'database' => $database,
'connections' => $connections,
'status' => $maxConnections && $connections >= $maxConnections ? '<fg=yellow;options=bold>ALERT</>' : '<fg=green;options=bold>OK</>',
];
});
}







protected function displayConnections($databases)
{
$this->newLine();

$this->components->twoColumnDetail('<fg=gray>Database name</>', '<fg=gray>Connections</>');

$databases->each(function ($database) {
$status = '['.$database['connections'].'] '.$database['status'];

$this->components->twoColumnDetail($database['database'], $status);
});

$this->newLine();
}







protected function dispatchEvents($databases)
{
$databases->each(function ($database) {
if ($database['status'] === '<fg=green;options=bold>OK</>') {
return;
}

$this->events->dispatch(
new DatabaseBusy(
$database['database'],
$database['connections']
)
);
});
}
}
