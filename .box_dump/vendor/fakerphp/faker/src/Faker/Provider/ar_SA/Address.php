<?php

namespace Faker\Provider\ar_SA;

class Address extends \Faker\Provider\Address
{
protected static $streetPrefix = ['شارع', 'طريق', 'ممر'];




protected static $cityName = [
'الرياض', 'جدة', 'مكة', 'المدينة المنورة', 'تبوك', 'الدمام', 'الأحساء', 'القطيف', 'خميس مشيط', 'المظيلف', 'الهفوف',
'المبرز', 'الطائف', 'نجران', 'حفر الباطن', 'الجبيل', 'ضباء', 'الخرج', 'الثقبة', 'ينبع البحر', 'الخبر', 'عرعر', 'الحوية',
'عنيزة', 'سكاكا', 'جيزان', 'القريات', 'الظهران', 'الزلفي', 'الباحة', 'الرس', 'وادي الدواسر', 'بيشة', 'سيهات', 'شرورة',
'الدوادمي', 'الأفلاج',
];




protected static $subdivisions = [
'منطقة الرياض', 'منطقة القصيم',
'منطقة مكة المكرمة', 'منطقة المدينة المنورة',
'منطقة حائل', 'منطقة الجوف', 'منطقة تبوك', 'منطقة الحدود الشمالية',
'منطقة عسير', 'منطقة جازان', 'منطقة نجران', 'منطقة الباحة',
'المنطقة الشرقية',
];




protected static $governorates = [
'الرياض', 'الدرعية', 'الخرج', 'الدوادمي', 'المجمعة', 'القويعية', 'الأفلاج', 'وادي الدواسر', 'الزلفي', 'شقراء', 'حوطة بني تميم', 'عفيف', 'الغاط', 'السليل', 'ضرما', 'المزاحمية', 'رماح', 'ثادق', 'حريملاء', 'الحريق', 'مرات',
'مكة المكرمة', 'جدة', 'الطائف', 'القنفذة', 'الليث', 'رابغ', 'خليص', 'الخرمة', 'رنية', 'تربة', 'الجموم', 'الكامل', 'المويه', 'ميسان', 'أضم', 'العرضيات', 'بحرة',
'المدينة المنورة', 'ينبع', 'العلا', 'مهد الذهب', 'الحناكية', 'بدر', 'خيبر', 'العيص', 'وادي الفرع',
'بريدة', 'عنيزة', 'الرس', 'المذنب', 'البكيرية', 'البدائع', 'الأسياح', 'النبهانية', 'الشماسية', 'عيون الجواء', 'رياض الخبراء', 'عقلة الصقور', 'ضرية',
'الدمام', 'الأحساء', 'حفر الباطن', 'الجبيل', 'القطيف', 'الخبر', 'الخفجي', 'رأس تنورة', 'بقيق', 'النعيرية', 'قرية العليا', 'العديد',
'أبها', 'خميس مشيط', 'بيشة', 'النماص', 'محايل عسير', 'ظهران الجنوب', 'تثليث', 'سراة عبيدة', 'رجال ألمع', 'بلقرن', 'أحد رفيدة', 'المجاردة', 'البرك', 'بارق', 'تنومة', 'طريب',
'تبوك', 'الوجه', 'ضبا', 'تيماء', 'أملج', 'حقل', 'البدع',
'حائل', 'بقعاء', 'الغزالة', 'الشنان', 'الحائط', 'السليمي', 'الشملي', 'موقق', 'سميراء',
'عرعر', 'رفحاء', 'طريف', 'العويقيلة',
'جازان', 'صبيا', 'أبو عريش', 'صامطة', 'بيش', 'الدرب', 'الحرث', 'ضمد', 'الريث', 'جزر فرسان', 'الدائر', 'العارضة', 'أحد المسارحة', 'العيدابي', 'فيفاء', 'الطوال', 'هروب',
'نجران', 'شرورة', 'حبونا', 'بدر الجنوب', 'يدمه', 'ثار', 'خباش', 'الخرخير',
'الباحة', 'بلجرشي', 'المندق', 'المخواة', 'قلوة', 'العقيق', 'القرى', 'غامد الزناد', 'الحجرة', 'بني حسن',
'سكاكا', 'القريات', 'دومة الجندل', 'طبرجل',
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




public static function subdivision()
{
return static::randomElement(static::$subdivisions);
}




public static function governorate()
{
return static::randomElement(static::$governorates);
}
}