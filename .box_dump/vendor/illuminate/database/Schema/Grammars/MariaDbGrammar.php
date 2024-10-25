<?php

namespace Illuminate\Database\Schema\Grammars;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;

class MariaDbGrammar extends MySqlGrammar
{








public function compileRenameColumn(Blueprint $blueprint, Fluent $command, Connection $connection)
{
if (version_compare($connection->getServerVersion(), '10.5.2', '<')) {
return $this->compileLegacyRenameColumn($blueprint, $command, $connection);
}

return parent::compileRenameColumn($blueprint, $command, $connection);
}







protected function typeUuid(Fluent $column)
{
return 'uuid';
}







protected function typeGeometry(Fluent $column)
{
$subtype = $column->subtype ? strtolower($column->subtype) : null;

if (! in_array($subtype, ['point', 'linestring', 'polygon', 'geometrycollection', 'multipoint', 'multilinestring', 'multipolygon'])) {
$subtype = null;
}

return sprintf('%s%s',
$subtype ?? 'geometry',
$column->srid ? ' ref_system_id='.$column->srid : ''
);
}
}