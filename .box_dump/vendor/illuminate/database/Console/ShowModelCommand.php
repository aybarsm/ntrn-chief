<?php

namespace Illuminate\Database\Console;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use SplFileObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;

use function Illuminate\Support\enum_value;

#[AsCommand(name: 'model:show')]
class ShowModelCommand extends DatabaseInspectionCommand
{





protected $name = 'model:show {model}';






protected $description = 'Show information about an Eloquent model';






protected $signature = 'model:show {model : The model to show}
                {--database= : The database connection to use}
                {--json : Output the model as JSON}';






protected $relationMethods = [
'hasMany',
'hasManyThrough',
'hasOneThrough',
'belongsToMany',
'hasOne',
'belongsTo',
'morphOne',
'morphTo',
'morphMany',
'morphToMany',
'morphedByMany',
];






public function handle()
{
$class = $this->qualifyModel($this->argument('model'));

try {
$model = $this->laravel->make($class);

$class = get_class($model);
} catch (BindingResolutionException $e) {
$this->components->error($e->getMessage());

return 1;
}

if ($this->option('database')) {
$model->setConnection($this->option('database'));
}

$this->display(
$class,
$model->getConnection()->getName(),
$model->getConnection()->getTablePrefix().$model->getTable(),
$this->getPolicy($model),
$this->getAttributes($model),
$this->getRelations($model),
$this->getEvents($model),
$this->getObservers($model),
);

return 0;
}







protected function getPolicy($model)
{
$policy = Gate::getPolicyFor($model::class);

return $policy ? $policy::class : null;
}







protected function getAttributes($model)
{
$connection = $model->getConnection();
$schema = $connection->getSchemaBuilder();
$table = $model->getTable();
$columns = $schema->getColumns($table);
$indexes = $schema->getIndexes($table);

return collect($columns)
->map(fn ($column) => [
'name' => $column['name'],
'type' => $column['type'],
'increments' => $column['auto_increment'],
'nullable' => $column['nullable'],
'default' => $this->getColumnDefault($column, $model),
'unique' => $this->columnIsUnique($column['name'], $indexes),
'fillable' => $model->isFillable($column['name']),
'hidden' => $this->attributeIsHidden($column['name'], $model),
'appended' => null,
'cast' => $this->getCastType($column['name'], $model),
])
->merge($this->getVirtualAttributes($model, $columns));
}








protected function getVirtualAttributes($model, $columns)
{
$class = new ReflectionClass($model);

return collect($class->getMethods())
->reject(
fn (ReflectionMethod $method) => $method->isStatic()
|| $method->isAbstract()
|| $method->getDeclaringClass()->getName() === Model::class
)
->mapWithKeys(function (ReflectionMethod $method) use ($model) {
if (preg_match('/^get(.+)Attribute$/', $method->getName(), $matches) === 1) {
return [Str::snake($matches[1]) => 'accessor'];
} elseif ($model->hasAttributeMutator($method->getName())) {
return [Str::snake($method->getName()) => 'attribute'];
} else {
return [];
}
})
->reject(fn ($cast, $name) => collect($columns)->contains('name', $name))
->map(fn ($cast, $name) => [
'name' => $name,
'type' => null,
'increments' => false,
'nullable' => null,
'default' => null,
'unique' => null,
'fillable' => $model->isFillable($name),
'hidden' => $this->attributeIsHidden($name, $model),
'appended' => $model->hasAppended($name),
'cast' => $cast,
])
->values();
}







protected function getRelations($model)
{
return collect(get_class_methods($model))
->map(fn ($method) => new ReflectionMethod($model, $method))
->reject(
fn (ReflectionMethod $method) => $method->isStatic()
|| $method->isAbstract()
|| $method->getDeclaringClass()->getName() === Model::class
|| $method->getNumberOfParameters() > 0
)
->filter(function (ReflectionMethod $method) {
if ($method->getReturnType() instanceof ReflectionNamedType
&& is_subclass_of($method->getReturnType()->getName(), Relation::class)) {
return true;
}

$file = new SplFileObject($method->getFileName());
$file->seek($method->getStartLine() - 1);
$code = '';
while ($file->key() < $method->getEndLine()) {
$code .= trim($file->current());
$file->next();
}

return collect($this->relationMethods)
->contains(fn ($relationMethod) => str_contains($code, '$this->'.$relationMethod.'('));
})
->map(function (ReflectionMethod $method) use ($model) {
$relation = $method->invoke($model);

if (! $relation instanceof Relation) {
return null;
}

return [
'name' => $method->getName(),
'type' => Str::afterLast(get_class($relation), '\\'),
'related' => get_class($relation->getRelated()),
];
})
->filter()
->values();
}







protected function getEvents($model)
{
return collect($model->dispatchesEvents())
->map(fn (string $class, string $event) => [
'event' => $event,
'class' => $class,
])->values();
}







protected function getObservers($model)
{
$listeners = $this->getLaravel()->make('events')->getRawListeners();


$listeners = array_filter($listeners, function ($v, $key) use ($model) {
return Str::startsWith($key, 'eloquent.') && Str::endsWith($key, $model::class);
}, ARRAY_FILTER_USE_BOTH);


$extractVerb = function ($key) {
preg_match('/eloquent.([a-zA-Z]+)\: /', $key, $matches);

return $matches[1] ?? '?';
};

$formatted = [];

foreach ($listeners as $key => $observerMethods) {
$formatted[] = [
'event' => $extractVerb($key),
'observer' => array_map(fn ($obs) => is_string($obs) ? $obs : 'Closure', $observerMethods),
];
}

return collect($formatted);
}














protected function display($class, $database, $table, $policy, $attributes, $relations, $events, $observers)
{
$this->option('json')
? $this->displayJson($class, $database, $table, $policy, $attributes, $relations, $events, $observers)
: $this->displayCli($class, $database, $table, $policy, $attributes, $relations, $events, $observers);
}














protected function displayJson($class, $database, $table, $policy, $attributes, $relations, $events, $observers)
{
$this->output->writeln(
collect([
'class' => $class,
'database' => $database,
'table' => $table,
'policy' => $policy,
'attributes' => $attributes,
'relations' => $relations,
'events' => $events,
'observers' => $observers,
])->toJson()
);
}














protected function displayCli($class, $database, $table, $policy, $attributes, $relations, $events, $observers)
{
$this->newLine();

$this->components->twoColumnDetail('<fg=green;options=bold>'.$class.'</>');
$this->components->twoColumnDetail('Database', $database);
$this->components->twoColumnDetail('Table', $table);

if ($policy) {
$this->components->twoColumnDetail('Policy', $policy);
}

$this->newLine();

$this->components->twoColumnDetail(
'<fg=green;options=bold>Attributes</>',
'type <fg=gray>/</> <fg=yellow;options=bold>cast</>',
);

foreach ($attributes as $attribute) {
$first = trim(sprintf(
'%s %s',
$attribute['name'],
collect(['increments', 'unique', 'nullable', 'fillable', 'hidden', 'appended'])
->filter(fn ($property) => $attribute[$property])
->map(fn ($property) => sprintf('<fg=gray>%s</>', $property))
->implode('<fg=gray>,</> ')
));

$second = collect([
$attribute['type'],
$attribute['cast'] ? '<fg=yellow;options=bold>'.$attribute['cast'].'</>' : null,
])->filter()->implode(' <fg=gray>/</> ');

$this->components->twoColumnDetail($first, $second);

if ($attribute['default'] !== null) {
$this->components->bulletList(
[sprintf('default: %s', $attribute['default'])],
OutputInterface::VERBOSITY_VERBOSE
);
}
}

$this->newLine();

$this->components->twoColumnDetail('<fg=green;options=bold>Relations</>');

foreach ($relations as $relation) {
$this->components->twoColumnDetail(
sprintf('%s <fg=gray>%s</>', $relation['name'], $relation['type']),
$relation['related']
);
}

$this->newLine();

$this->components->twoColumnDetail('<fg=green;options=bold>Events</>');

if ($events->count()) {
foreach ($events as $event) {
$this->components->twoColumnDetail(
sprintf('%s', $event['event']),
sprintf('%s', $event['class']),
);
}
}

$this->newLine();

$this->components->twoColumnDetail('<fg=green;options=bold>Observers</>');

if ($observers->count()) {
foreach ($observers as $observer) {
$this->components->twoColumnDetail(
sprintf('%s', $observer['event']),
implode(', ', $observer['observer'])
);
}
}

$this->newLine();
}








protected function getCastType($column, $model)
{
if ($model->hasGetMutator($column) || $model->hasSetMutator($column)) {
return 'accessor';
}

if ($model->hasAttributeMutator($column)) {
return 'attribute';
}

return $this->getCastsWithDates($model)->get($column) ?? null;
}







protected function getCastsWithDates($model)
{
return collect($model->getDates())
->filter()
->flip()
->map(fn () => 'datetime')
->merge($model->getCasts());
}








protected function getColumnDefault($column, $model)
{
$attributeDefault = $model->getAttributes()[$column['name']] ?? null;

return enum_value($attributeDefault, $column['default']);
}








protected function attributeIsHidden($attribute, $model)
{
if (count($model->getHidden()) > 0) {
return in_array($attribute, $model->getHidden());
}

if (count($model->getVisible()) > 0) {
return ! in_array($attribute, $model->getVisible());
}

return false;
}








protected function columnIsUnique($column, $indexes)
{
return collect($indexes)->contains(
fn ($index) => count($index['columns']) === 1 && $index['columns'][0] === $column && $index['unique']
);
}









protected function qualifyModel(string $model)
{
if (str_contains($model, '\\') && class_exists($model)) {
return $model;
}

$model = ltrim($model, '\\/');

$model = str_replace('/', '\\', $model);

$rootNamespace = $this->laravel->getNamespace();

if (Str::startsWith($model, $rootNamespace)) {
return $model;
}

return is_dir(app_path('Models'))
? $rootNamespace.'Models\\'.$model
: $rootNamespace.$model;
}
}
