# تسليم رشفة

- [x] **التجربة العامة:** تنفيذ الصفحة الرئيسية RTL المستجيبة: Hero بزجاجة WebGL وبديل صورة، سكة نكهات أفقية بمشاهد ماكرو وأرقام شفافة، قصة «نقطف/نعصر/نرشف» sticky، بطاقات مكونات تقلب مع تحكم قابل للوصول، مخصص رشفة حي وزر طلب WhatsApp.
- [x] **طبقة البيانات:** ربط الواجهة بجداول `products`, `categories`, `product_variants`, `product_story_phases`, `ingredients`, `product_ingredients`, `product_images`, `media_library`, و`site_settings`، مع نموذج بيانات/Seed ومكتبة وسائط تجعل المواد المرفقة ظاهرة فعلياً.
- [x] **بوابة الإدارة السرية:** مسار `/secret-portal-admin-99x` لا يظهر في التصفح العام، حارس `admin` من جدول `admins`، كلمات مرور Hash فقط، CSRF، rate limit، قفل مؤقت، session regeneration، وتفويض أدوار viewer/editor/super_admin.
- [x] **إدارة دون كود:** شاشات لإدارة المنتجات والتصنيفات والمكونات والمتغيرات ومراحل القصة والوسائط وإعدادات الموقع، مع عمليات CRUD متحققة ومعاملات آمنة.
- [x] **الجودة والتسليم:** تكوين Tailwind/Vite/Three.js، استجابة هاتف/سطح مكتب، reduced motion، تحقق PHP وVite، README عربي، وأرشيف ZIP خالٍ من الأسرار وnode_modules/vendor.
