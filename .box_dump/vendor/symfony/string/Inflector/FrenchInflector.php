<?php










namespace Symfony\Component\String\Inflector;






final class FrenchInflector implements InflectorInterface
{





private const PLURALIZE_REGEXP = [





['/(s|x|z)$/i', '\1'],



['/(eau)$/i', '\1x'],



['/^(landau)$/i', '\1s'],
['/(au)$/i', '\1x'],



['/^(pneu|bleu|émeu)$/i', '\1s'],
['/(eu)$/i', '\1x'],



['/^(bal|carnaval|caracal|chacal|choral|corral|étal|festival|récital|val)$/i', '\1s'],
['/al$/i', '\1aux'],


['/^(aspir|b|cor|ém|ferm|soupir|trav|vant|vitr)ail$/i', '\1aux'],


['/^(bij|caill|ch|gen|hib|jouj|p)ou$/i', '\1oux'],


['/^(cinquante|soixante|mille)$/i', '\1'],


['/^(mon|ma)(sieur|dame|demoiselle|seigneur)$/', 'mes\2s'],
['/^(Mon|Ma)(sieur|dame|demoiselle|seigneur)$/', 'Mes\2s'],
];




private const SINGULARIZE_REGEXP = [




['/((aspir|b|cor|ém|ferm|soupir|trav|vant|vitr))aux$/i', '\1ail'],



['/(eau)x$/i', '\1'],



['/(amir|anim|arsen|boc|can|capit|capor|chev|crist|génér|hopit|hôpit|idé|journ|littor|loc|m|mét|minér|princip|radic|termin)aux$/i', '\1al'],



['/(au)x$/i', '\1'],



['/(eu)x$/i', '\1'],



['/(bij|caill|ch|gen|hib|jouj|p)oux$/i', '\1ou'],


['/^mes(dame|demoiselle)s$/', 'ma\1'],
['/^Mes(dame|demoiselle)s$/', 'Ma\1'],
['/^mes(sieur|seigneur)s$/', 'mon\1'],
['/^Mes(sieur|seigneur)s$/', 'Mon\1'],


['/s$/i', ''],
];





private const UNINFLECTED = '/^(abcès|accès|abus|albatros|anchois|anglais|autobus|bois|brebis|carquois|cas|chas|colis|concours|corps|cours|cyprès|décès|devis|discours|dos|embarras|engrais|entrelacs|excès|fils|fois|gâchis|gars|glas|héros|intrus|jars|jus|kermès|lacis|legs|lilas|marais|mars|matelas|mépris|mets|mois|mors|obus|os|palais|paradis|parcours|pardessus|pays|plusieurs|poids|pois|pouls|printemps|processus|progrès|puits|pus|rabais|radis|recors|recours|refus|relais|remords|remous|rictus|rhinocéros|repas|rubis|sans|sas|secours|sens|souris|succès|talus|tapis|tas|taudis|temps|tiers|univers|velours|verglas|vernis|virus)$/i';

public function singularize(string $plural): array
{
if ($this->isInflectedWord($plural)) {
return [$plural];
}

foreach (self::SINGULARIZE_REGEXP as $rule) {
[$regexp, $replace] = $rule;

if (1 === preg_match($regexp, $plural)) {
return [preg_replace($regexp, $replace, $plural)];
}
}

return [$plural];
}

public function pluralize(string $singular): array
{
if ($this->isInflectedWord($singular)) {
return [$singular];
}

foreach (self::PLURALIZE_REGEXP as $rule) {
[$regexp, $replace] = $rule;

if (1 === preg_match($regexp, $singular)) {
return [preg_replace($regexp, $replace, $singular)];
}
}

return [$singular.'s'];
}

private function isInflectedWord(string $word): bool
{
return 1 === preg_match(self::UNINFLECTED, $word);
}
}
