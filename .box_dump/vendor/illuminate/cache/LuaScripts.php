<?php

namespace Illuminate\Cache;

class LuaScripts
{








public static function releaseLock()
{
return <<<'LUA'
if redis.call("get",KEYS[1]) == ARGV[1] then
    return redis.call("del",KEYS[1])
else
    return 0
end
LUA;
}
}