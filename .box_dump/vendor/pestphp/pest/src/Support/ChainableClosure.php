<?php

declare(strict_types=1);

namespace Pest\Support;

use Closure;
use Pest\Exceptions\ShouldNotHappen;




final class ChainableClosure
{



public static function boundWhen(Closure $condition, Closure $next): Closure
{
return function () use ($condition, $next): void {
if (! is_object($this)) { 
throw ShouldNotHappen::fromMessage('$this not bound to chainable closure.');
}

if (\Pest\Support\Closure::bind($condition, $this, self::class)(...func_get_args())) {
\Pest\Support\Closure::bind($next, $this, self::class)(...func_get_args());
}
};
}




public static function bound(Closure $closure, Closure $next): Closure
{
return function () use ($closure, $next): void {
if (! is_object($this)) { 
throw ShouldNotHappen::fromMessage('$this not bound to chainable closure.');
}

\Pest\Support\Closure::bind($closure, $this, self::class)(...func_get_args());
\Pest\Support\Closure::bind($next, $this, self::class)(...func_get_args());
};
}




public static function unbound(Closure $closure, Closure $next): Closure
{
return function () use ($closure, $next): void {
$closure(...func_get_args());
$next(...func_get_args());
};
}




public static function boundStatically(Closure $closure, Closure $next): Closure
{
return static function () use ($closure, $next): void {
\Pest\Support\Closure::bind($closure, null, self::class)(...func_get_args());
\Pest\Support\Closure::bind($next, null, self::class)(...func_get_args());
};
}
}
