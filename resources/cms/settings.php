<?php

/*
|--------------------------------------------------------------------------
| مخطط محتوى الموقع (CMS Schema)
|--------------------------------------------------------------------------
| هذا الملف هو المصدر الوحيد لكل النصوص والصور القابلة للتعديل.
| كل مجموعة = تبويب في لوحة التحكم. كل حقل = مفتاح في جدول site_settings.
| الأنواع: text | textarea | url | image | toggle | select | palette
| كل حقل text/textarea له نسخة إنجليزية تلقائياً (تُحفظ تحت المفتاح key__en)؛ أضف 'i18n' => false لاستثناء حقل تقني.
| القيمة الافتراضية 'default' تُستخدم حتى يحفظ المدير قيمة جديدة.
| لإضافة حقل جديد: أضفه هنا ثم استخدم setting('المفتاح', 'الافتراضي') في Blade.
*/

return [

    'general' => [
        'label' => 'عام',
        'icon' => '◐',
        'fields' => [
            'site_title'       => ['type' => 'text',     'label' => 'عنوان الموقع (تبويب المتصفح)', 'default' => 'رشة | غرفة تذوق رقمية'],
            'site_description' => ['type' => 'textarea', 'label' => 'وصف الموقع (محركات البحث)',   'default' => 'رشة — عصائر طازجة بنكهات تغيّر لون كل شيء.'],
            'brand_name'       => ['type' => 'text',     'label' => 'اسم العلامة (الشعار النصي)',   'default' => 'رشة', 'help' => 'الحرف الأول يظهر داخل دائرة.'],
        ],
    ],

    'colors' => [
        'label' => 'الألوان',
        'icon' => '◉',
        'fields' => [
            'brand_palette' => ['type' => 'palette', 'label' => 'لوحة ألوان العلامة', 'default' => '#FF7A1A,#7BB82D,#FFB300,#F06A8D,#4B4FB8', 'help' => 'اضغط على أي لون لتغييره، أو أضف لوناً جديداً. هذه الألوان تظهر كاختصارات عند اختيار لون أي نص.'],
        ],
    ],

    'sunbird' => [
        'label' => 'شاشة سن بيرد',
        'icon' => '✦',
        'fields' => [
            'sunbird_logo'       => ['type' => 'image', 'label' => 'شعار سن بيرد', 'default' => 'media/rashfa/sunbird-logo.png'],
            'sunbird_name'       => ['type' => 'text',  'label' => 'اسم يظهر إذا تعذّر تحميل الشعار', 'default' => 'Sun Bird'],
            'sunbird_logo_alt'   => ['type' => 'text',  'label' => 'النص البديل للشعار', 'default' => 'سن بيرد Sun Bird'],
            'sunbird_tagline'    => ['type' => 'text',  'label' => 'الشعار الدعائي', 'default' => 'انتعاش الطبيعة في كل رشفة'],
            'sunbird_cta'        => ['type' => 'text',  'label' => 'نص الزر', 'default' => 'اكتشف منتجاتنا'],
            'sunbird_site_label' => ['type' => 'text',  'label' => 'نص رابط الموقع', 'default' => 'زيارة موقعنا'],
        ],
    ],

    'contact' => [
        'label' => 'التواصل',
        'icon' => '✆',
        'fields' => [
            'contact.whatsapp' => ['type' => 'text', 'label' => 'رقم واتساب (مع رمز الدولة)', 'default' => '', 'help' => 'مثال: 966500000000', 'i18n' => false],
            'social.instagram' => ['type' => 'url',  'label' => 'رابط إنستغرام', 'default' => 'https://instagram.com/'],
            'social.x'         => ['type' => 'url',  'label' => 'رابط إكس (تويتر)', 'default' => 'https://x.com/'],
            'contact.website'  => ['type' => 'url',  'label' => 'رابط الموقع الرسمي', 'default' => '#'],
            'contact.email'    => ['type' => 'text', 'label' => 'البريد الإلكتروني', 'default' => 'hello@rashfa.test', 'i18n' => false],
            'contact.map_url'  => ['type' => 'text', 'i18n' => false, 'ltr' => true, 'normalize_url' => true, 'label' => 'رابط موقع المصنع (خرائط جوجل)', 'default' => '', 'help' => 'افتح خرائط جوجل ← ابحث عن المصنع ← «مشاركة» ← انسخ الرابط والصقه هنا. (يمكن أيضاً كتابة الإحداثيات مثل 24.7136, 46.6753). اتركه فارغاً لإخفاء زر الموقع.'],
            'contact.map_label' => ['type' => 'text', 'label' => 'نص زر موقع المصنع', 'default' => 'موقع المصنع على الخريطة'],
        ],
    ],

    'nav' => [
        'label' => 'القائمة العلوية',
        'icon' => '☰',
        'fields' => [
            'nav_flavors' => ['type' => 'text', 'label' => 'رابط النكهات', 'default' => 'النكهات'],
            'nav_story'   => ['type' => 'text', 'label' => 'رابط الحكاية', 'default' => 'الحكاية'],
            'nav_sunbird' => ['type' => 'text', 'label' => 'رابط نبذة عن سن بيرد', 'default' => 'نبذة عن سن بيرد'],
            'nav_order'   => ['type' => 'text', 'label' => 'زر القائمة', 'default' => 'تصفّح النكهات'],
        ],
    ],

    'hero' => [
        'label' => 'الواجهة الرئيسية',
        'icon' => '★',
        'fields' => [
            'hero_title'        => ['type' => 'text', 'styleable' => true,     'label' => 'العنوان الرئيسي', 'default' => 'رشة تُصمَّم على ذوقك.', 'help' => 'أول كلمة وآخر كلمة تظهران بتنسيق خاص. اكتب | لتحديد مكان السطر الجديد (اختياري).'],
            'hero_word'         => ['type' => 'text',     'label' => 'الكلمة الكبيرة في الخلفية', 'default' => 'رشة'],
            'hero_eyebrow'      => ['type' => 'text', 'styleable' => true,     'label' => 'النص الصغير فوق العنوان', 'default' => 'غرفة تذوق رقمية'],
            'hero_bottle_image' => ['type' => 'image',    'label' => 'صورة الزجاجة', 'default' => 'media/rashfa/bottle-poster.webp'],
            'hero_bottle_alt'   => ['type' => 'text',     'label' => 'النص البديل للزجاجة', 'default' => 'زجاجة عصير رشة'],
            'hero_tag_number'   => ['type' => 'text',     'label' => 'رقم بطاقة الموسم', 'default' => '01', 'i18n' => false],
            'hero_tag_label'    => ['type' => 'text',     'label' => 'عنوان بطاقة الموسم', 'default' => 'إصدار الموسم'],
            'hero_tag_value'    => ['type' => 'text',     'label' => 'نكهة الموسم', 'default' => 'خوخ مشمشي'],
            'hero_badge'        => ['type' => 'text',     'label' => 'الشارة الدائرية', 'default' => 'طازج ١٠٠٪', 'help' => 'الكلمة الأخيرة تظهر بخط أكبر في سطر ثانٍ.'],
            'hero_lead'         => ['type' => 'textarea', 'styleable' => true, 'label' => 'الوصف التعريفي', 'default' => 'عصائر طازجة، وكل نكهة تلوّن الأزرار والبطاقات والتوهج. اختر نكهتك وشاهد اللمعة تتغيّر.'],
            'hero_cta_primary'  => ['type' => 'text',     'label' => 'الزر الأساسي', 'default' => 'اكتشف النكهات'],
            'hero_cta_secondary'=> ['type' => 'text',     'label' => 'الرابط الثانوي', 'default' => 'اقرأ الحكاية'],
            'hero_proof_1'      => ['type' => 'text',     'label' => 'ميزة ١', 'default' => 'مكوّنات حقيقية'],
            'hero_proof_2'      => ['type' => 'text',     'label' => 'ميزة ٢', 'default' => 'يُحضّر عند الطلب'],
            'hero_scroll_cue'   => ['type' => 'text',     'label' => 'نص التمرير', 'default' => 'اسحب لتتذوق'],
        ],
    ],

    'flavors' => [
        'label' => 'قسم النكهات',
        'icon' => '❖',
        'fields' => [
            'flavors_eyebrow' => ['type' => 'text',     'label' => 'النص الصغير', 'default' => 'اختَر مزاج الرشة'],
            'flavors_title'   => ['type' => 'text', 'styleable' => true,     'label' => 'العنوان', 'default' => 'رحلة النكهات.'],
            'flavors_lead'    => ['type' => 'textarea', 'styleable' => true, 'label' => 'الوصف', 'default' => 'مرّر الماوس فوق أي بطاقة وشاهد الموقع كله يتلوّن بنكهتها.'],
            'currency'        => ['type' => 'text',     'label' => 'رمز العملة', 'default' => 'ر.س'],
        ],
    ],

    'story' => [
        'label' => 'الحكاية',
        'icon' => '❝',
        'fields' => [
            'story_source'  => ['type' => 'select', 'label' => 'مصدر المراحل', 'default' => 'settings',
                                'options' => ['settings' => 'من هذه الصفحة (الأسهل)', 'database' => 'من مراحل الحكاية المسجلة للنكهة'],
                                'help' => 'اختر «من هذه الصفحة» لتعديل المراحل الثلاث هنا مباشرة.'],
            'story_eyebrow' => ['type' => 'text',     'label' => 'النص الصغير', 'default' => 'من البستان إلى الرشة'],
            'story_title'   => ['type' => 'text', 'styleable' => true,     'label' => 'العنوان', 'default' => 'لا نُسرّع الطعم.'],
            'story_lead'    => ['type' => 'textarea', 'styleable' => true, 'label' => 'الوصف', 'default' => 'ثلاث لحظات فقط تحفظ النكهة كما يجب أن تكون.'],

            'story_label_harvest' => ['type' => 'text', 'label' => 'تسمية المرحلة ١', 'default' => 'من البستان'],
            'story_p1_title' => ['type' => 'text',     'label' => 'المرحلة ١ — العنوان', 'default' => 'نقطف في أوانها'],
            'story_p1_body'  => ['type' => 'textarea', 'label' => 'المرحلة ١ — النص', 'default' => 'نختار الفاكهة حين تقول النكهة نعم.'],
            'story_p1_image' => ['type' => 'image',    'label' => 'المرحلة ١ — الصورة', 'default' => 'media/rashfa/photo-1600271886742-f049cd451bba.jpeg'],

            'story_label_press' => ['type' => 'text', 'label' => 'تسمية المرحلة ٢', 'default' => 'في غرفة العصر'],
            'story_p2_title' => ['type' => 'text',     'label' => 'المرحلة ٢ — العنوان', 'default' => 'نعصر على مهل'],
            'story_p2_body'  => ['type' => 'textarea', 'label' => 'المرحلة ٢ — النص', 'default' => 'نعصر الفاكهة بلطف حتى يبقى الطعم صافياً.'],
            'story_p2_image' => ['type' => 'image',    'label' => 'المرحلة ٢ — الصورة', 'default' => 'media/rashfa/photo-1614707585284-9cb9fc018387.jpeg'],

            'story_label_sip' => ['type' => 'text', 'label' => 'تسمية المرحلة ٣', 'default' => 'إلى يدك'],
            'story_p3_title' => ['type' => 'text',     'label' => 'المرحلة ٣ — العنوان', 'default' => 'نرشف بلا استعجال'],
            'story_p3_body'  => ['type' => 'textarea', 'label' => 'المرحلة ٣ — النص', 'default' => 'تُحضَّر الرشة عند الطلب لتصلك طازجة.'],
            'story_p3_image' => ['type' => 'image',    'label' => 'المرحلة ٣ — الصورة', 'default' => 'media/rashfa/bottle-poster.webp'],
        ],
    ],

    'sunbird_story' => [
        'label' => 'نبذة عن سن بيرد',
        'icon' => '☀',
        'fields' => [
            'sunbird_story_eyebrow' => ['type' => 'text', 'styleable' => true, 'label' => 'النص الصغير فوق العنوان', 'default' => 'قسم المشروبات والعصائر'],
            'sunbird_story_title'   => ['type' => 'text', 'styleable' => true, 'label' => 'العنوان', 'default' => 'نبذة عن سن بيرد', 'help' => 'آخر كلمة تظهر بتمييز لوني. اكتب | لتحديد مكان السطر الجديد (اختياري).'],
            'sunbird_story_lead'    => ['type' => 'textarea', 'styleable' => true, 'label' => 'نبذة عن القسم', 'default' => 'نقدم في قسم المشروبات والعصائر بـ سن بيرد تشكيلة مميزة من المشروبات المصنوعة بعناية فائقة لتلبي أذواق جميع المستهلكين. نعتمد على أجود الفواكه الطبيعية والمكونات المختارة بعناية لتقديم طعم غني، منعش، وصحي يتماشى مع نمط الحياة العصري.'],

            'sunbird_story_products_label' => ['type' => 'text', 'label' => 'تسمية تشكيلة المنتجات', 'default' => 'تشكيلة المنتجات'],
            'sunbird_story_products_title' => ['type' => 'text', 'styleable' => true, 'label' => 'عنوان المنتج', 'default' => 'عصائر الفواكه الطبيعية 100%'],
            'sunbird_story_products_body'  => ['type' => 'textarea', 'styleable' => true, 'label' => 'وصف المنتج', 'default' => 'عصائر طازجة ومنعشة غنية بالفيتامينات والمعادن الأساسية، خالية تماماً من المواد الحافظة والألوان الصناعية.'],

            'sunbird_story_chip_1' => ['type' => 'text', 'label' => 'ميزة ١', 'default' => 'طازجة ومنعشة'],
            'sunbird_story_chip_2' => ['type' => 'text', 'label' => 'ميزة ٢', 'default' => 'غنية بالفيتامينات والمعادن'],
            'sunbird_story_chip_3' => ['type' => 'text', 'label' => 'ميزة ٣', 'default' => 'خالية من المواد الحافظة'],
            'sunbird_story_chip_4' => ['type' => 'text', 'label' => 'ميزة ٤', 'default' => 'بلا ألوان صناعية'],
            'sunbird_story_cta'    => ['type' => 'text', 'label' => 'نص الزر (يقود إلى النكهات)', 'default' => 'اكتشف نكهاتنا'],

            'sunbird_story_bg'   => ['type' => 'image', 'label' => 'صورة الخلفية السينمائية', 'default' => 'media/rashfa/photo-1600271886742-f049cd451bba.jpeg'],
            'sunbird_story_img1' => ['type' => 'image', 'label' => 'الصورة العائمة ١', 'default' => 'media/rashfa/kiwi.webp'],
            'sunbird_story_img2' => ['type' => 'image', 'label' => 'الصورة العائمة ٢', 'default' => 'media/rashfa/photo-1614707585284-9cb9fc018387.jpeg'],
        ],
    ],

    'ingredients' => [
        'label' => 'المكوّنات',
        'icon' => '✿',
        'fields' => [
            'ingredients_eyebrow' => ['type' => 'text',     'label' => 'النص الصغير', 'default' => 'ما في الزجاجة؟'],
            'ingredients_title'   => ['type' => 'text', 'styleable' => true,     'label' => 'العنوان', 'default' => 'المكوّن يتكلم.'],
            'ingredients_lead'    => ['type' => 'textarea', 'styleable' => true, 'label' => 'الوصف', 'default' => 'نحافظ على القائمة قصيرة حتى تبقى النكهة واضحة.'],
            'ingredient_btn_more' => ['type' => 'text',     'label' => 'زر قلب البطاقة', 'default' => 'اعرف المصدر'],
            'ingredient_btn_back' => ['type' => 'text',     'label' => 'زر العودة', 'default' => 'عودة'],
            'ingredient_back_eyebrow' => ['type' => 'text', 'label' => 'عنوان ظهر البطاقة', 'default' => 'أصل المكوّن'],
            'ingredient_default_desc' => ['type' => 'textarea', 'label' => 'وصف افتراضي للمكوّن', 'default' => 'نختاره يدوياً من موسمٍ واضح ونتركه يتكلم بنكهته.'],
            'ingredient_fact1_label' => ['type' => 'text', 'label' => 'معلومة ١ — العنوان', 'default' => 'القطف'],
            'ingredient_fact1_value' => ['type' => 'text', 'label' => 'معلومة ١ — القيمة', 'default' => 'يدوي'],
            'ingredient_fact2_label' => ['type' => 'text', 'label' => 'معلومة ٢ — العنوان', 'default' => 'المصدر'],
            'ingredient_fact2_value' => ['type' => 'text', 'label' => 'معلومة ٢ — القيمة', 'default' => 'موسمي'],
            'ingredient_img_1' => ['type' => 'image', 'label' => 'صورة البطاقة ١', 'default' => 'media/rashfa/photo-1600271886742-f049cd451bba.jpeg'],
            'ingredient_img_2' => ['type' => 'image', 'label' => 'صورة البطاقة ٢', 'default' => 'media/rashfa/photo-1614707585284-9cb9fc018387.jpeg'],
            'ingredient_img_3' => ['type' => 'image', 'label' => 'صورة البطاقة ٣', 'default' => 'media/rashfa/kiwi.webp'],
            'ingredient_img_4' => ['type' => 'image', 'label' => 'صورة البطاقة ٤', 'default' => 'media/rashfa/photo-1603569283847-aa295f0d016a.jpeg'],
            'ingredient_img_5' => ['type' => 'image', 'label' => 'صورة البطاقة ٥', 'default' => 'media/rashfa/bottle-poster.webp'],
            'ingredient_img_6' => ['type' => 'image', 'label' => 'صورة البطاقة ٦', 'default' => 'media/rashfa/photo-1534353473418-4cfa6c56fd38.jpeg'],
        ],
    ],

    'footer' => [
        'label' => 'الفوتر',
        'icon' => '▾',
        'fields' => [
            'footer_eyebrow'     => ['type' => 'text', 'styleable' => true,  'label' => 'النص الصغير', 'default' => 'رشة أخبار موسمية'],
            'footer_title'       => ['type' => 'text', 'styleable' => true,  'label' => 'العنوان الدعائي', 'default' => 'ابقَ قريباً من الموسم.'],
            'footer_logo'        => ['type' => 'image', 'label' => 'الشعار الدائري', 'default' => 'media/rashfa/غلاف شعار شكل داري.png'],
            'footer_logo_alt'    => ['type' => 'text',  'label' => 'النص البديل للشعار', 'default' => 'سن بيرد'],
            'footer_word_back'   => ['type' => 'text',  'label' => 'الكلمة العملاقة (خلفية)', 'default' => 'SUN BIRD', 'i18n' => false],
            'footer_word_front'  => ['type' => 'text',  'label' => 'الكلمة العملاقة (أمامية)', 'default' => 'رشة', 'i18n' => false],
            'footer_newsletter_label'       => ['type' => 'text', 'styleable' => true, 'label' => 'عنوان حقل البريد', 'default' => 'بريدك الإلكتروني'],
            'footer_newsletter_placeholder' => ['type' => 'text', 'label' => 'نص داخل حقل البريد', 'default' => 'name@example.com', 'i18n' => false],
            'footer_link_home'   => ['type' => 'text', 'styleable' => true,  'label' => 'رابط ١', 'default' => 'الرئيسية'],
            'footer_link_flavors'=> ['type' => 'text', 'styleable' => true,  'label' => 'رابط ٢', 'default' => 'النكهات'],
            'footer_link_story'  => ['type' => 'text', 'styleable' => true,  'label' => 'رابط ٣', 'default' => 'الحكاية'],
            'footer_tagline'     => ['type' => 'text', 'styleable' => true,  'label' => 'الجملة السفلية', 'default' => 'صُنعت على مهل، لتُرتشف على مهل.'],
            'footer_site_label'  => ['type' => 'text', 'styleable' => true,  'label' => 'نص رابط الموقع', 'default' => 'موقعنا'],
            'footer_contact_label' => ['type' => 'text', 'styleable' => true, 'label' => 'نص رابط التواصل', 'default' => 'تواصل'],
        ],
    ],
];
