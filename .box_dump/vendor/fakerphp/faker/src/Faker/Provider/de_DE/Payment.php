<?php

namespace Faker\Provider\de_DE;

class Payment extends \Faker\Provider\Payment
{











public static function bankAccountNumber($prefix = '', $countryCode = 'DE', $length = null)
{
return static::iban($countryCode, $prefix, $length);
}











protected static $banks = [
'Bank 1 Saar', 'Bayerische Landesbank', 'BBBank', 'Berliner Sparkasse', 'Berliner Volksbank', 'Braunschweigische Landessparkasse',
'Commerzbank',
'DekaBank Deutsche Girozentrale', 'Deutsche Apotheker- und Ärztebank', 'Deutsche Bank', 'Deutsche Kreditbank', 'Deutsche Pfandbriefbank', 'Dortmunder Volksbank', 'DZ Bank',
'Erzgebirgssparkasse',
'Frankfurter Sparkasse', 'Frankfurter Volksbank',
'Hamburger Sparkasse', 'Hannoversche Volksbank', 'HSGV', 'HSH Nordbank',
'ING-DiBa',
'KfW', 'Kreissparkasse Esslingen-Nürtingen', 'Kreissparkasse Heilbronn', 'Kreissparkasse Köln', 'Kreissparkasse Ludwigsburg', 'Kreissparkasse München Starnberg Ebersberg',
'L-Bank', 'Landesbank Baden-Württemberg', 'Landesbank Hessen-Thüringen', 'Landessparkasse zu Oldenburg', 'Landwirtschaftliche Rentenbank',
'Mittelbrandenburgische Sparkasse in Potsdam',
'Nassauische Sparkasse', 'Norddeutsche Landesbank', 'NRW.Bank',
'Ostsächsische Sparkasse Dresden',
'Postbank',
'Sparkasse Hannover', 'Sparkasse KölnBonn', 'Sparkasse Mainfranken Würzburg', 'Sparkasse Nürnberg', 'Sparkasse Pforzheim Calw', 'Stadtsparkasse München',
'Unicredit Bank',
'Vereinigte Volksbank', 'Volksbank, Hildesheim-Lehrte-Pattensen', 'Volksbank Alzey-Worms', 'Volksbank Braunschweig Wolfsburg', 'Volksbank Darmstadt - Südhessen', 'Volksbank Hohenlohe', 'Volksbank Kraichgau Wiesloch-Sinsheim', 'Volksbank Lüneburger Heide', 'Volksbank Mittelhessen', 'Volksbank Paderborn-Höxter-Detmold', 'Volksbank Raiffeisenbank Rosenheim-Chiemsee', 'Volksbank Stuttgart', 'VR Bank Main-Kinzig-Büdingen',
'WGZ Bank',
];




public static function bank()
{
return static::randomElement(static::$banks);
}
}
