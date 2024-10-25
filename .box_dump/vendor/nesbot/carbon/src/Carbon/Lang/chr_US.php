<?php














return array_replace_recursive(require __DIR__.'/en.php', [
'formats' => [
'L' => 'MM/DD/YYYY',
],
'months' => ['ᎤᏃᎸᏔᏅ', 'ᎧᎦᎵ', 'ᎠᏅᏱ', 'ᎧᏬᏂ', 'ᎠᏂᏍᎬᏘ', 'ᏕᎭᎷᏱ', 'ᎫᏰᏉᏂ', 'ᎦᎶᏂ', 'ᏚᎵᏍᏗ', 'ᏚᏂᏅᏗ', 'ᏅᏓᏕᏆ', 'ᎥᏍᎩᏱ'],
'months_short' => ['ᎤᏃ', 'ᎧᎦ', 'ᎠᏅ', 'ᎧᏬ', 'ᎠᏂ', 'ᏕᎭ', 'ᎫᏰ', 'ᎦᎶ', 'ᏚᎵ', 'ᏚᏂ', 'ᏅᏓ', 'ᎥᏍ'],
'weekdays' => ['ᎤᎾᏙᏓᏆᏍᎬ', 'ᎤᎾᏙᏓᏉᏅᎯ', 'ᏔᎵᏁᎢᎦ', 'ᏦᎢᏁᎢᎦ', 'ᏅᎩᏁᎢᎦ', 'ᏧᎾᎩᎶᏍᏗ', 'ᎤᎾᏙᏓᏈᏕᎾ'],
'weekdays_short' => ['ᏆᏍᎬ', 'ᏉᏅᎯ', 'ᏔᎵᏁ', 'ᏦᎢᏁ', 'ᏅᎩᏁ', 'ᏧᎾᎩ', 'ᏈᏕᎾ'],
'weekdays_min' => ['ᏆᏍᎬ', 'ᏉᏅᎯ', 'ᏔᎵᏁ', 'ᏦᎢᏁ', 'ᏅᎩᏁ', 'ᏧᎾᎩ', 'ᏈᏕᎾ'],
'first_day_of_week' => 0,
'day_of_first_week_of_year' => 1,
'meridiem' => ['ᏌᎾᎴ', 'ᏒᎯᏱᎢᏗᏢ', 'ꮜꮎꮄ', 'ꮢꭿᏹꭲꮧꮲ'],

'second' => ':count ᏐᎢ', 
's' => ':count ᏐᎢ', 
'a_second' => ':count ᏐᎢ', 

'year' => ':count ᏑᏕᏘᏴᏓ',
'y' => ':count ᏑᏕᏘᏴᏓ',
'a_year' => ':count ᏑᏕᏘᏴᏓ',

'month' => ':count ᏏᏅᏙ',
'm' => ':count ᏏᏅᏙ',
'a_month' => ':count ᏏᏅᏙ',

'week' => ':count ᏑᎾᏙᏓᏆᏍᏗ',
'w' => ':count ᏑᎾᏙᏓᏆᏍᏗ',
'a_week' => ':count ᏑᎾᏙᏓᏆᏍᏗ',

'day' => ':count ᎢᎦ',
'd' => ':count ᎢᎦ',
'a_day' => ':count ᎢᎦ',

'hour' => ':count ᏑᏟᎶᏛ',
'h' => ':count ᏑᏟᎶᏛ',
'a_hour' => ':count ᏑᏟᎶᏛ',

'minute' => ':count ᎢᏯᏔᏬᏍᏔᏅ',
'min' => ':count ᎢᏯᏔᏬᏍᏔᏅ',
'a_minute' => ':count ᎢᏯᏔᏬᏍᏔᏅ',

'ago' => ':time ᏥᎨᏒ',
'from_now' => 'ᎾᎿ :time',
]);
