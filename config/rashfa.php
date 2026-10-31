<?php

return [
    'whatsapp_number' => env('RASHFA_WHATSAPP_NUMBER', '966500000000'),
    'brand_name' => 'رشفة',
    'admin_lock_minutes' => 15,

    // لغة الزائر الافتراضية للواجهة العامة — مستقلة عن APP_LOCALE في ملف .env (الذي يكون en افتراضياً في لارافيل)
    'default_locale' => 'ar',

    // اللغات المدعومة في الواجهة العامة (لوحة التحكم تبقى بالعربية)
    'locales' => [
        'ar' => ['name' => 'العربية', 'short' => 'ع',  'dir' => 'rtl'],
        'en' => ['name' => 'English', 'short' => 'EN', 'dir' => 'ltr'],
    ],

    // Preview deployments must never mutate the existing catalog or upload files.
    'preview_read_only' => (bool) env('RASHFA_PREVIEW_READ_ONLY', true),

    // Optional existing HTTPS media origin. No database URL rewriting is required.
    'media_base_url' => env('RASHFA_MEDIA_BASE_URL'),
];
