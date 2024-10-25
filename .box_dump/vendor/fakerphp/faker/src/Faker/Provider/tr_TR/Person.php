<?php

namespace Faker\Provider\tr_TR;

class Person extends \Faker\Provider\Person
{



protected static $maleNameFormats = [
'{{firstNameMale}} {{lastName}}',
'{{firstNameMale}} {{lastName}}',
'{{firstNameMale}} {{lastName}}',
'{{titleMale}} {{firstNameMale}} {{lastName}}',
];

protected static $femaleNameFormats = [
'{{firstNameFemale}} {{lastName}}',
'{{firstNameFemale}} {{lastName}}',
'{{firstNameFemale}} {{lastName}}',
'{{titleFemale}} {{firstNameFemale}} {{lastName}}',
];






protected static $firstNameMale = [
'Ahmet', 'Ali', 'Alp', 'Armağan', 'Atakan', 'Aşkın', 'Baran', 'Bartu', 'Berk', 'Berkay', 'Berke', 'Bora', 'Burak', 'Canberk',
'Cem', 'Cihan', 'Deniz', 'Efe', 'Ege', 'Ege', 'Emir', 'Emirhan', 'Emre', 'Ferid', 'Göktürk', 'Görkem', 'Güney',
'Kağan', 'Kerem', 'Koray', 'Kutay', 'Mert', 'Onur', 'Ogün', 'Polat', 'Rüzgar', 'Sarp', 'Serhan', 'Toprak', 'Tuna',
'Türker', 'Utku', 'Yağız', 'Yiğit', 'Çınar', 'Derin', 'Meriç', 'Barlas', 'Dağhan', 'Doruk', 'Çağan',
];






protected static $firstNameFemale = [
'Ada', 'Esma', 'Emel', 'Ebru', 'Şahnur', 'Ümran', 'Sinem', 'İrem', 'Rüya', 'Ece', 'Burcu',
];






protected static $lastName = [
'Abacı', 'Abadan', 'Aclan', 'Adal', 'Adan', 'Adıvar', 'Akal', 'Akan', 'Akar', 'Akay',
'Akaydın', 'Akbulut', 'Akgül', 'Akışık', 'Akman', 'Akyürek', 'Akyüz', 'Akşit', 'Alnıaçık',
'Alpuğan', 'Alyanak', 'Arıcan', 'Arslanoğlu', 'Atakol', 'Atan', 'Avan', 'Ayaydın', 'Aybar',
'Aydan', 'Aykaç', 'Ayverdi', 'Ağaoğlu', 'Aşıkoğlu', 'Babacan', 'Babaoğlu', 'Bademci',
'Bakırcıoğlu', 'Balaban', 'Balcı', 'Barbarosoğlu', 'Baturalp', 'Baykam', 'Başoğlu', 'Berberoğlu',
'Beşerler', 'Beşok', 'Biçer', 'Bolatlı', 'Dalkıran', 'Dağdaş', 'Dağlaroğlu', 'Demirbaş', 'Demirel',
'Denkel', 'Dizdar', 'Doğan', 'Durak', 'Durmaz', 'Duygulu', 'Düşenkalkar', 'Egeli', 'Ekici', 'Ekşioğlu',
'Eliçin', 'Elmastaşoğlu', 'Elçiboğa', 'Erbay', 'Erberk', 'Erbulak', 'Erdoğan', 'Erez', 'Erginsoy',
'Erkekli', 'Eronat', 'Ertepınar', 'Ertürk', 'Erçetin', 'Evliyaoğlu', 'Fahri', 'Gönültaş', 'Gümüşpala',
'Günday', 'Gürmen', 'Ilıcalı', 'Kahveci', 'Kaplangı', 'Karabulut', 'Karaböcek', 'Karadaş', 'Karaduman',
'Karaer', 'Kasapoğlu', 'Kavaklıoğlu', 'Kaya', 'Keseroğlu', 'Keçeci', 'Kılıççı', 'Kıraç', 'Kocabıyık',
'Korol', 'Koyuncu', 'Koç', 'Koçoğlu', 'Koçyiğit', 'Kuday', 'Kulaksızoğlu', 'Kumcuoğlu', 'Kunt',
'Kunter', 'Kurutluoğlu', 'Kutlay', 'Kuzucu', 'Körmükçü', 'Köybaşı', 'Köylüoğlu', 'Küçükler', 'Limoncuoğlu',
'Mayhoş', 'Menemencioğlu', 'Mertoğlu', 'Nalbantoğlu', 'Nebioğlu', 'Numanoğlu', 'Okumuş', 'Okur', 'Oraloğlu',
'Orbay', 'Ozansoy', 'Paksüt', 'Pekkan', 'Pektemek', 'Polat', 'Poyrazoğlu', 'Poçan', 'Sadıklar', 'Samancı',
'Sandalcı', 'Sarıoğlu', 'Saygıner', 'Sepetçi', 'Sezek', 'Sinanoğlu', 'Solmaz', 'Sözeri', 'Süleymanoğlu',
'Tahincioğlu', 'Tanrıkulu', 'Tazegül', 'Taşlı', 'Taşçı', 'Tekand', 'Tekelioğlu', 'Tokatlıoğlu', 'Tokgöz',
'Topaloğlu', 'Topçuoğlu', 'Toraman', 'Tunaboylu', 'Tunçeri', 'Tuğlu', 'Tuğluk', 'Türkdoğan', 'Türkyılmaz',
'Tütüncü', 'Tüzün', 'Uca', 'Uluhan', 'Velioğlu', 'Yalçın', 'Yazıcı', 'Yetkiner', 'Yeşilkaya', 'Yıldırım',
'Yıldızoğlu', 'Yılmazer', 'Yorulmaz', 'Çamdalı', 'Çapanoğlu', 'Çatalbaş', 'Çağıran', 'Çetin', 'Çetiner',
'Çevik', 'Çörekçi', 'Önür', 'Örge', 'Öymen', 'Özberk', 'Özbey', 'Özbir', 'Özdenak', 'Özdoğan', 'Özgörkey',
'Özkara', 'Özkök', 'Öztonga', 'Öztuna',
];

protected static $title = ['Doç. Dr.', 'Dr.', 'Prof. Dr.'];

public function title($gender = null)
{
return static::titleMale();
}




public static function titleMale()
{
return static::randomElement(static::$title);
}




public static function titleFemale()
{
return static::titleMale();
}








public function tcNo()
{
$randomDigits = static::numerify('#########');
$checksum = self::tcNoChecksum($randomDigits);

return $randomDigits . $checksum;
}











public static function tcNoChecksum($identityPrefix)
{
if (strlen((string) $identityPrefix) !== 9) {
throw new \InvalidArgumentException('Argument should be an integer and should be 9 digits.');
}

$oddSum = 0;
$evenSum = 0;

$identityArray = array_map('intval', str_split($identityPrefix)); 

foreach ($identityArray as $index => $digit) {
if ($index % 2 === 0) {
$evenSum += $digit;
} else {
$oddSum += $digit;
}
}

$tenthDigit = (7 * $evenSum - $oddSum) % 10;
$eleventhDigit = ($evenSum + $oddSum + $tenthDigit) % 10;

return $tenthDigit . $eleventhDigit;
}








public static function tcNoIsValid($tcNo)
{
return self::tcNoChecksum(substr($tcNo, 0, -2)) === substr($tcNo, -2, 2);
}
}
