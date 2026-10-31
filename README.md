# رشفة — Rashfa Luxury Tasting Room

مشروع Laravel 11 عربي (RTL) يحوّل «رشفة» إلى غرفة تذوق رقمية بنمط **Warm Darkness**: أسود عميق، أخضر زمردي، ذهب هادئ ولمسات ليمونية. يضم واجهة Blade/Tailwind مع Three.js، وطبقة إدارة مخفية مرتبطة فقط بجداول `juice_database` المرفقة.

## ما الذي تم تسليمه؟

- صفحة رئيسية متجاوبة بالكامل: مشهد افتتاحي بزجاجة WebGL مع بديل صورة، معرض نكهات أفقي، قصة مثبتة من ثلاث مراحل، بطاقات مكونات ثلاثية الأبعاد، ومخصص «اصنع رشفتك» يولّد طلب WhatsApp جاهزاً.
- واجهة عربية RTL مع دعم لوحة المفاتيح، تباين عالٍ، وتحويل تلقائي إلى تجربة أخف عند `prefers-reduced-motion`.
- لوحة سرية على: `/secret-portal-admin-99x`.
- حارس `admin` منفصل عن أي `users`، يقرأ جدول `admins` وعمود `password_hash` مباشرةً، مع CSRF، تجديد الجلسة، Rate Limiting، قفل مؤقت للحساب، وتفويض `viewer / editor / super_admin`.
- CRUD فعلي للنكهات، التصنيفات، المكونات، الصور والوسائط، الأحجام/المتغيرات، مراحل القصة، وإعدادات الموقع العامة.
- نماذج Eloquent مترابطة مع جداول: `products`, `categories`, `product_variants`, `product_story_phases`, `product_images`, `media_library`, `ingredients`, `product_ingredients`, `admins`, `site_settings`.

## المتطلبات

- PHP 8.2+ مع امتدادات: `mbstring`, `xml`, `curl`, `zip`, `pdo_mysql`.
- Composer 2.
- Node.js 20+ وnpm.
- **MySQL 8.0+**. المخطط الأصلي المرفق يعتمد قيود MySQL 8 وعموداً مولّداً لضمان صورة رئيسية وحيدة لكل منتج.

## التثبيت السريع

```bash
cd rashfa
cp .env.example .env
composer install
npm install
php artisan key:generate
```

حرّر قيم `DB_*` في `.env` لتشير إلى قاعدة MySQL باسم `juice_database`، ثم نفّذ:

```bash
php artisan migrate --seed
npm run build
php artisan storage:link
```

تشمل البذور ست نكهات، المكونات، المتغيرات، مراحل قصة البرتقال، إعدادات اتصال عامة، وربط الأصول الموجودة في `public/media/rashfa` داخل مكتبة الوسائط.

> لا تُنشئ البذور أي مدير افتراضي ولا توجد كلمات مرور داخل المشروع.

## إنشاء أول مسؤول

بعد الترحيل، أنشئ مسؤولاً بشكل تفاعلي؛ كلمة المرور لا تمر في الملفات ولا تظهر في السجل:

```bash
php artisan rashfa:make-admin admin@example.com --name="اسم المسؤول" --role=super_admin
```

الأدوار:

- `viewer`: عرض لوحة الإدارة فقط.
- `editor`: إدارة الكتالوج والوسائط والقصص والمتغيرات.
- `super_admin`: صلاحيات editor بالإضافة لإعدادات الموقع.

ثم افتح المسار السري:

```text
/secret-portal-admin-99x
```

## التطوير والبناء

```bash
npm run dev
php artisan serve
```

للإنتاج:

```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

لـPreview HTTPS المضمّن اضبط `SESSION_SECURE_COOKIE=true` و`SESSION_SAME_SITE=none` في بيئة الإنتاج فقط، مع HTTPS فعلي.

## قاعدة البيانات المرفقة

- المصدر الأصلي محفوظ بلا تعديل في `database/reference/rashfa_schema_seed.original.sql` و`database/reference/rashfa_queries.reference.sql` للمراجعة والمرجعية.
- ترحيل Laravel المكافئ موجود في `database/migrations/2026_10_21_000000_create_rashfa_catalog_schema.php`.
- إذا استوردت SQL الأصلي مباشرةً بدلاً من تشغيل Laravel migrations، لا تشغّل `migrate` فوق الجداول الموجودة؛ شغّل فقط البذور التي تحتاجها بعد مراجعة مسارات الوسائط.

## تخصيصات التشغيل

- رقم الطلب يقرأ من `RASHFA_WHATSAPP_NUMBER` أو `site_settings.contact.whatsapp`.
- CTA والمحتوى العام قابلان للتعديل من `site_settings` بصيغة JSON؛ لا تجعل مفتاحاً عاماً ما لم يكن آمناً للواجهة.
- احفظ صور المنتج في مكتبة الوسائط من لوحة الإدارة، ثم اربطها بالنكهة وحدد الصورة الرئيسية من شاشة الصور.
- تظل الأسعار بيانات عرض فقط؛ لا يضيف هذا المشروع checkout أو دفعاً أو عملاء لأن قاعدة البيانات المرفقة لا تحتوي جداولها.

## التحقق المنفّذ قبل التسليم

- `php -l` على ملفات التطبيق والمهاجرات والإعدادات.
- `php artisan route:list` و`php artisan view:cache`.
- `npm run build` مع Vite/Tailwind وThree.js.
- `php artisan migrate:fresh --seed` على قاعدة اختبار MySQL متوافقة، بنتيجة: 6 نكهات، 10 وسائط، و3 مراحل قصة.
- اختبار HTTP للصفحة الرئيسية وبوابة الدخول والأصول، واختبار تحديث مخصص الرشفة ورابط WhatsApp.

## ملاحظات الحزمة

الأرشيف يستثني عمداً: `.env`، `vendor/`، `node_modules/`، سجلات التطبيق، الملفات المؤقتة، وقواعد الاختبار. استخدم `composer install` و`npm install` بعد فك الضغط. الأصول وصورة البناء الإنتاجي ضمن الحزمة.
