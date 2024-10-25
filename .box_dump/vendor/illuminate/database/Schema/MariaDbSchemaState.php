<?php

namespace Illuminate\Database\Schema;

class MariaDbSchemaState extends MySqlSchemaState
{





protected function baseDumpCommand()
{
$command = 'mysqldump '.$this->connectionString().' --no-tablespaces --skip-add-locks --skip-comments --skip-set-charset --tz-utc --column-statistics=0';

return $command.' "${:LARAVEL_LOAD_DATABASE}"';
}
}
