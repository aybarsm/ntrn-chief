<?php














return array_replace_recursive(require __DIR__.'/en.php', [
'formats' => [
'L' => 'D-M-YY',
],
'months' => ['जानेवारी', 'फेब्रुवारी', 'मार्च', 'एप्रिल', 'मे', 'जून', 'जुलै', 'ओगस्ट', 'सेप्टेंबर', 'ओक्टोबर', 'नोव्हेंबर', 'डिसेंबर'],
'months_short' => ['जानेवारी', 'फेब्रुवारी', 'मार्च', 'एप्रिल', 'मे', 'जून', 'जुलै', 'ओगस्ट', 'सेप्टेंबर', 'ओक्टोबर', 'नोव्हेंबर', 'डिसेंबर'],
'weekdays' => ['आयतार', 'सोमार', 'मंगळवार', 'बुधवार', 'बेरेसतार', 'शुकरार', 'शेनवार'],
'weekdays_short' => ['आयतार', 'सोमार', 'मंगळवार', 'बुधवार', 'बेरेसतार', 'शुकरार', 'शेनवार'],
'weekdays_min' => ['आयतार', 'सोमार', 'मंगळवार', 'बुधवार', 'बेरेसतार', 'शुकरार', 'शेनवार'],
'first_day_of_week' => 0,
'day_of_first_week_of_year' => 1,
'meridiem' => ['म.पू.', 'म.नं.'],

'year' => ':count वैशाकु', 
'y' => ':count वैशाकु', 
'a_year' => ':count वैशाकु', 

'week' => ':count आदित्यवार', 
'w' => ':count आदित्यवार', 
'a_week' => ':count आदित्यवार', 

'minute' => ':count नोंद', 
'min' => ':count नोंद', 
'a_minute' => ':count नोंद', 

'second' => ':count तेंको', 
's' => ':count तेंको', 
'a_second' => ':count तेंको', 

'month' => ':count मैनो',
'm' => ':count मैनो',
'a_month' => ':count मैनो',

'day' => ':count दिवसु',
'd' => ':count दिवसु',
'a_day' => ':count दिवसु',

'hour' => ':count घंते',
'h' => ':count घंते',
'a_hour' => ':count घंते',
]);
