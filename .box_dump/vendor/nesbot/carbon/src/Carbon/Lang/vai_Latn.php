<?php










return array_replace_recursive(require __DIR__.'/en.php', [
'weekdays' => ['lahadi', 'tɛɛnɛɛ', 'talata', 'alaba', 'aimisa', 'aijima', 'siɓiti'],
'weekdays_short' => ['lahadi', 'tɛɛnɛɛ', 'talata', 'alaba', 'aimisa', 'aijima', 'siɓiti'],
'weekdays_min' => ['lahadi', 'tɛɛnɛɛ', 'talata', 'alaba', 'aimisa', 'aijima', 'siɓiti'],
'months' => ['luukao kemã', 'ɓandaɓu', 'vɔɔ', 'fulu', 'goo', '6', '7', 'kɔnde', 'saah', 'galo', 'kenpkato ɓololɔ', 'luukao lɔma'],
'months_short' => ['luukao kemã', 'ɓandaɓu', 'vɔɔ', 'fulu', 'goo', '6', '7', 'kɔnde', 'saah', 'galo', 'kenpkato ɓololɔ', 'luukao lɔma'],
'first_day_of_week' => 1,
'formats' => [
'LT' => 'h:mm a',
'LTS' => 'h:mm:ss a',
'L' => 'DD/MM/YYYY',
'LL' => 'D MMM YYYY',
'LLL' => 'D MMMM YYYY h:mm a',
'LLLL' => 'dddd, D MMMM YYYY h:mm a',
],
]);
