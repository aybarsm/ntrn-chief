<?php










return array_replace_recursive(require __DIR__.'/ff.php', [
'formats' => [
'LT' => 'h:mm a',
'LTS' => 'h:mm:ss a',
'L' => 'D/M/YYYY',
'LL' => 'D MMM, YYYY',
'LLL' => 'D MMMM YYYY h:mm a',
'LLLL' => 'dddd D MMMM YYYY h:mm a',
],
]);
