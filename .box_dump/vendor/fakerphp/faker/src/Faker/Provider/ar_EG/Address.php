<?php

namespace Faker\Provider\ar_EG;

class Address extends \Faker\Provider\Address
{
protected static $cityPrefix = [
'شمال',
'جنوب',
'شرق',
'غرب',
];

protected static $streetPrefix = ['شارع', 'طريق', 'ممر'];




protected static $cityName = [
'التجمع الاول',
'التجمع التالت',
'التجمع الخامس',
'الشروق',
'الرحاب',
'الجزيرة',
'الحسين',
'الزمالك',
'السلام',
'الظاهر',
'العباسية',
'المطرية',
'الموسكي',
'النزهة الجديدة',
'السيدة زينب',
'المرج',
'المعادي',
'المقطم',
'المنيل',
'الوايلي',
'باب الشعرية',
'باب اللوق',
'ثكنات المعادي',
'جاردن سيتي',
'جسر السويس',
'عابدين',
'حدائق المعادي',
'حلمية الزيتون',
'حلوان',
'الأزبكية',
'الزاوية الحمراء',
'الساحل',
'مدينة نصر',
'حدائق القبة',
'شبرا',
'عين شمس',
'روكسي',
'زهراء المعادي',
'سراي القبة',
'عبود',
'عزبة النخل',
'كوتسيكا',
'الشيخ زايد',
'السادس من اكتوير',
'العاشر من رمضان',
'المعصرة',
'الزهراء',
'غمرة',
'المنيب',
'فيصل',
'الدقي',
'العتبة',
'المظلات',
'المطار',
'قباء',
'ألف مسكن',
'هليوبوليس',
'هارون',
'كلية البنات',
'عبده باشا',
'الجيش',
'الكيت كات',
'إمبابة',
];





protected static $governorates = [
'الإسكندرية' => '02',
'الإسماعيلية' => '19',
'أسوان' => '28',
'أسيوط' => '25',
'الأقصر' => '29',
'البحر الأحمر' => '31',
'البحيرة' => '18',
'بني سويف' => '22',
'بورسعيد' => '03',
'جنوب سيناء' => '35',
'القاهرة' => '01',
'الدقهلية' => '12',
'دمياط' => '11',
'سوهاج' => '26',
'السويس' => '04',
'الشرقية' => '13',
'شمال سيناء' => '34',
'الغربية' => '16',
'الفيوم' => '23',
'القليوبية' => '14',
'قنا' => '27',
'كفر الشيخ' => '15',
'مطروح' => '33',
'المنوفية' => '17',
'المنيا' => '24',
'الوادي الجديد' => '32',
];

protected static $buildingNumber = ['%####', '%###', '%#'];

protected static $postcode = ['#####', '#####-####'];




protected static $country = [
'الكاريبي', 'أمريكا الوسطى', 'أنتيجوا وبربودا', 'أنجولا', 'أنجويلا', 'أندورا', 'اندونيسيا', 'أورجواي', 'أوروبا', 'أوزبكستان', 'أوغندا', 'أوقيانوسيا', 'أوقيانوسيا النائية', 'أوكرانيا', 'ايران', 'أيرلندا', 'أيسلندا', 'ايطاليا',
'بابوا غينيا الجديدة', 'باراجواي', 'باكستان', 'بالاو', 'بتسوانا', 'بتكايرن', 'بربادوس', 'برمودا', 'بروناي', 'بلجيكا', 'بلغاريا', 'بليز', 'بنجلاديش', 'بنما', 'بنين', 'بوتان', 'بورتوريكو', 'بوركينا فاسو', 'بوروندي', 'بولندا', 'بوليفيا', 'بولينيزيا', 'بولينيزيا الفرنسية', 'بيرو',
'تانزانيا', 'تايلند', 'تايوان', 'تركمانستان', 'تركيا', 'ترينيداد وتوباغو', 'تشاد', 'توجو', 'توفالو', 'توكيلو', 'تونجا', 'تونس', 'تيمور الشرقية',
'جامايكا', 'جبل طارق', 'جرينادا', 'جرينلاند', 'جزر الأنتيل الهولندية', 'جزر الترك وجايكوس', 'جزر القمر', 'جزر الكايمن', 'جزر المارشال', 'جزر الملديف', 'جزر الولايات المتحدة البعيدة الصغيرة', 'جزر أولان', 'جزر سليمان', 'جزر فارو', 'جزر فرجين الأمريكية', 'جزر فرجين البريطانية', 'جزر فوكلاند', 'جزر كوك', 'جزر كوكوس', 'جزر ماريانا الشمالية', 'جزر والس وفوتونا', 'جزيرة الكريسماس', 'جزيرة بوفيه', 'جزيرة مان', 'جزيرة نورفوك', 'جزيرة هيرد وماكدونالد', 'جمهورية افريقيا الوسطى', 'جمهورية التشيك', 'جمهورية الدومينيك', 'جمهورية الكونغو الديمقراطية', 'جمهورية جنوب افريقيا', 'جنوب آسيا', 'جنوب أوروبا', 'جنوب شرق آسيا', 'جنوب وسط آسيا', 'جواتيمالا', 'جوادلوب', 'جوام', 'جورجيا', 'جورجيا الجنوبية وجزر ساندويتش الجنوبية', 'جيبوتي', 'جيرسي',
'دومينيكا',
'رواندا', 'روسيا', 'روسيا البيضاء', 'رومانيا', 'روينيون',
'زامبيا', 'زيمبابوي',
'ساحل العاج', 'ساموا', 'ساموا الأمريكية', 'سانت بيير وميكولون', 'سانت فنسنت وغرنادين', 'سانت كيتس ونيفيس', 'سانت لوسيا', 'سانت مارتين', 'سانت هيلنا', 'سان مارينو', 'ساو تومي وبرينسيبي', 'سريلانكا', 'سفالبارد وجان مايان', 'سلوفاكيا', 'سلوفينيا', 'سنغافورة', 'سوازيلاند', 'سوريا', 'سورينام', 'سويسرا', 'سيراليون', 'سيشل',
'شرق آسيا', 'شرق افريقيا', 'شرق أوروبا', 'شمال افريقيا', 'شمال أمريكا', 'شمال أوروبا', 'شيلي',
'صربيا', 'صربيا والجبل الأسود',
'طاجكستان',
'عمان',
'غامبيا', 'غانا', 'غرب آسيا', 'غرب افريقيا', 'غرب أوروبا', 'غويانا', 'غيانا', 'غينيا', 'غينيا الاستوائية', 'غينيا بيساو',
'فانواتو', 'فرنسا', 'فلسطين', 'فنزويلا', 'فنلندا', 'فيتنام', 'فيجي',
'قبرص', 'قرغيزستان', 'قطر',
'كازاخستان', 'كاليدونيا الجديدة', 'كرواتيا', 'كمبوديا', 'كندا', 'كوبا', 'كوريا الجنوبية', 'كوريا الشمالية', 'كوستاريكا', 'كولومبيا', 'كومنولث الدول المستقلة', 'كيريباتي', 'كينيا',
'لاتفيا', 'لاوس', 'لبنان', 'لوكسمبورج', 'ليبيا', 'ليبيريا', 'ليتوانيا', 'ليختنشتاين', 'ليسوتو',
'مارتينيك', 'ماكاو الصينية', 'مالطا', 'مالي', 'ماليزيا', 'مايوت', 'مدغشقر', 'مصر', 'مقدونيا', 'ملاوي', 'منغوليا', 'موريتانيا', 'موريشيوس', 'موزمبيق', 'مولدافيا', 'موناكو', 'مونتسرات', 'ميانمار', 'ميكرونيزيا', 'ميلانيزيا',
'ناميبيا', 'نورو', 'نيبال', 'نيجيريا', 'نيكاراجوا', 'نيوزيلاندا', 'نيوي',
'هايتي', 'هندوراس', 'هولندا', 'هونج كونج الصينية',
'وسط آسيا', 'وسط افريقيا',
];

protected static $cityFormats = [
'{{cityName}}',
];

protected static $streetNameFormats = [
'{{streetPrefix}} {{firstName}} {{lastName}}',
];

protected static $streetAddressFormats = [
'{{buildingNumber}} {{streetName}}',
'{{buildingNumber}} {{streetName}} {{secondaryAddress}}',
];

protected static $addressFormats = [
"{{streetAddress}}\n{{city}}",
];

protected static $secondaryAddressFormats = ['شقة رقم. ##', 'عمارة رقم ##'];




public static function cityPrefix()
{
return static::randomElement(static::$cityPrefix);
}




public static function cityName()
{
return static::randomElement(static::$cityName);
}




public static function streetPrefix()
{
return static::randomElement(static::$streetPrefix);
}




public static function secondaryAddress()
{
return static::numerify(static::randomElement(static::$secondaryAddressFormats));
}




public static function governorate()
{
return static::randomKey(static::$governorates);
}






public static function governorateId()
{
return static::randomElement(static::$governorates);
}
}
