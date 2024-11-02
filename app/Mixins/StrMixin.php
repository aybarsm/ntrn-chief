<?php

namespace App\Mixins;

/** @mixin \Illuminate\Support\Str */
class StrMixin
{
    public static function removeEmptyLines(): \Closure
    {
        return function (string $str): string {
            $init = static::replaceMatches('/^\s*[\r\n]+|[\r\n]+\s*\z/', '', $str);

            return static::replaceMatches('/(\n\s*){2,}/', "\n", $init);
        };
    }

    public static function matchesReplace(): \Closure
    {
        return function (string $pattern, array $segments): string
        {
            $cleaned = preg_replace('/\\\\/', '', trim($pattern, '/'));
            $split = mb_str_split($cleaned);
            [$found, $search, $replace] = [[], [], []];

            foreach($split as $pos => $char) {
                $cur = blank($found) ? null : array_key_last($found);

                if ($pos < 4){
                    continue;
                }

                if (($cur === null || isset($found[$cur]['end'])) && $split[$pos-4] === '(' && $split[$pos-3] === '?' && $split[$pos-2] === 'P' && $split[$pos-1] === '<'){
                    $found[] = ['start' => $pos-4, 'key' => $char, 'keyDone' => false, 'inner' => 0, 'search' => "(?P<{$char}"];
                }elseif ($cur !== null && ! isset($found[$cur]['end']) && $found[$cur]['keyDone'] === false){
                    if ($char === '>'){
                        $found[$cur]['keyDone'] = true;
                    }else {
                        $found[$cur]['key'] .= $char;
                    }
                }elseif ($cur !== null && ! isset($found[$cur]['end']) && $found[$cur]['keyDone'] === true) {
                    if ($char === ')' && $found[$cur]['inner'] % 2 == 0){
                        $found[$cur]['end'] = $pos;
                        // This won't be added at the end, so add it here
                        $found[$cur]['search'] .= $char;
                        if (isset($segments[$found[$cur]['key']])){
                            $search[] = $found[$cur]['search'];
                            $replace[] = $segments[$found[$cur]['key']];
                        }
                    }elseif ($char === '(' || $char === ')') {
                        $found[$cur]['inner']++;
                    }
                }

                if ($cur !== null && ! isset($found[$cur]['end'])){
                    $found[$cur]['search'] .= $char;
                }
            }

            return static::replace($search, $replace, $cleaned);
        };
    }
}
