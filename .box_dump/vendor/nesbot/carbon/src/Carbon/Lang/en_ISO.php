<?php










return array_replace_recursive(require __DIR__.'/en.php', [
'formats' => [
'LT' => 'HH:mm',
'LTS' => 'HH:mm:ss',
'L' => 'YYYY-MM-dd',
'LL' => 'YYYY MMM D',
'LLL' => 'YYYY MMMM D HH:mm',
'LLLL' => 'dddd, YYYY MMMM DD HH:mm',
],
'first_day_of_week' => 0,
]);
