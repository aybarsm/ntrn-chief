<?php

namespace Faker\Provider\fr_CA;

class Address extends \Faker\Provider\fr_FR\Address
{
protected static $cityPrefix = ['Saint-', 'Sainte-', 'St-', 'Ste-'];





protected static $citySuffix = [

'-des-Sables', '-sur-Mer', '-des-Neiges', '-des-Sept-Douleurs', '-du-Portage', '-du-Loup', '-des-Lacs', '-de-Lessard',
'-de-Kamourasca', '-de-Témiscouata', '-de-Ladrière', '-de-Rimouski', '-de-Rivière-du-Loup', '-du-Lac', '-du-Ha! Ha!',
'-du-Lac-Long', '-de-Rioux', '-du-Squatec', '-de-Métis', '-d\'Ixworth', '-de-la-Croix', '-de-Matane', '-du-Lac-Humqui',
'-de-Mérici', '-de-la-Pocatière', '-sur-le-Lac',

'-de-Lorette', '-du-Lac-Saint-Jean', '-de-Bourget', '-de-Falardeau', '-les-Plaines', '-de-Sales', '-de-Taillon',
'-de-Milot', '-du-Nord',

'-aux-Coudres', '-des-Anges', '-de-Desmaures', '-les-Neiges', '-de-l\'Île-d\'Orléans', '-de-Valcartier',
'-de-Portneuf', '-du-Cap-Tourmente', '-des-Carrières', '-des-Caps', '-de-Beaupré', '-de-Laval', '-de-la-Jacques-Cartier',
'-d\'Auvergne',

'-de-Monteauban', '-du-Mont-Carmel', '-des-Monts', '-de-Maskinongé', '-de-Caxton', '-des-Grès', '-le-Grand',
'-de-Vincennes', '-du-Parc', '-de-Champlain', '-de-Mékinac', '-de-Prémont', '-de-la-Pérade', '-de-Batiscan',

'-Ouest', '-Est', '-Sud', '-Nord', '-des-Bois', '-de-Woburn', '-de-Brompton', '-de-Bolton', '-de-Windsor',
'-de-Clifton', '-de-Paquette', '-de-la-Rochelle', '-de-Hatley', '-de-Whitton',

'-de-Bellevue',

'-de-Buckland', '-des-Pins', '-du-Rosaire', '-d\'Issoudun', '-de-Jésus', '-d\'Irlande', '-de-l\'Isle-aux-Grues',
'-de-Tilly', '-de-Lellis', '-de-Bellechasse', '-de-Lessard', '-de-L\'Islet', '-de-Lotbinière', '-de-Beauce',
'-de-Forsyth', '-de-Panet', '-de-la-Rivière-du-Sud', '-de-Dorset', '-de-Shenley', '-de-Leeds', '-de-Wolfestown',
'-de-Joly', '-de-Brébeuf', '-de-Coleraine', '-des-Érables', '-Bretenières', '-de-Lauzon', '-de-Standon',
'-de-Gonzague', '-de-Beaurivage', '-de-Dorchester', '-de-Cranbourne', '-de-Broughton', '-de-la-Rivière-du-Sud',
'-des-Aulnaies', '-les-Mines', '-de-Lotbinière', '-de-Patton', '-sur-Rivière-du-Sud', '-de-Beauregard', '-de-Watford',
];




protected static $cityFormats = [
'{{cityPrefix}}{{firstName}}{{citySuffix}}',
'{{cityPrefix}}{{firstName}}',
];

protected static $buildingNumber = ['%####', '%###', '%##', '%#', '%'];

protected static $streetSuffix = [
'Autoroute', 'Avenue', 'Boulevard', 'Chemin', 'Route', 'Rue', 'Pont',
];

protected static $postcode = ['?#? #?#', '?#?#?#'];




protected static $streetNameFormats = [
'{{streetSuffix}} {{firstName}}',
'{{streetSuffix}} {{lastName}}',
];

protected static $streetAddressFormats = [
'{{buildingNumber}} {{streetName}}',
'{{buildingNumber}} {{streetName}} {{secondaryAddress}}',
];

protected static $addressFormats = [
'{{streetAddress}}, {{city}}, {{stateAbbr}} {{postcode}}',
];

protected static $secondaryAddressFormats = ['Apt. ###', 'Suite ###', 'Bureau ###'];

protected static $state = [
'Alberta', 'Colombie-Britannique', 'Manitoba', 'Nouveau-Brunswick', 'Terre-Neuve-et-Labrador', 'Nouvelle-Écosse', 'Ontario', 'Île-du-Prince-Édouard', 'Québec', 'Saskatchewan',
];

protected static $stateAbbr = [
'AB', 'BC', 'MB', 'NB', 'NL', 'NS', 'ON', 'PE', 'QC', 'SK',
];




public static function cityPrefix()
{
return static::randomElement(static::$cityPrefix);
}




public static function citySuffix()
{
return static::randomElement(static::$citySuffix);
}




public static function secondaryAddress()
{
return static::numerify(static::randomElement(static::$secondaryAddressFormats));
}




public static function state()
{
return static::randomElement(static::$state);
}




public static function stateAbbr()
{
return static::randomElement(static::$stateAbbr);
}
}
