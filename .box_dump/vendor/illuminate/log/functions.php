<?php

namespace Illuminate\Log;

if (! function_exists('Illuminate\Log\log')) {







function log($message = null, array $context = [])
{
return logger($message, $context);
}
}
