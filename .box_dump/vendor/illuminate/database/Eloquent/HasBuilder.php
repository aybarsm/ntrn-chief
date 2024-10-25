<?php

namespace Illuminate\Database\Eloquent;

/**
@template
*/
trait HasBuilder
{





public static function query()
{
return parent::query();
}







public function newEloquentBuilder($query)
{
return parent::newEloquentBuilder($query);
}






public function newQuery()
{
return parent::newQuery();
}






public function newModelQuery()
{
return parent::newModelQuery();
}






public function newQueryWithoutRelationships()
{
return parent::newQueryWithoutRelationships();
}






public function newQueryWithoutScopes()
{
return parent::newQueryWithoutScopes();
}







public function newQueryWithoutScope($scope)
{
return parent::newQueryWithoutScope($scope);
}







public function newQueryForRestoration($ids)
{
return parent::newQueryForRestoration($ids);
}







public static function on($connection = null)
{
return parent::on($connection);
}






public static function onWriteConnection()
{
return parent::onWriteConnection();
}







public static function with($relations)
{
return parent::with($relations);
}
}
