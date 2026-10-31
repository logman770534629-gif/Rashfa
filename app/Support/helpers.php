<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

/*
|--------------------------------------------------------------------------
| دوال المحتوى — المصدر الوحيد لقراءة إعدادات الموقع
|--------------------------------------------------------------------------
| setting('key', 'default')         → قيمة نصية
| setting_style('key')              → مصفوفة التنسيق المحفوظة تحت المفتاح key__style
| setting_style_attr('key')         → ' style="..." data-styled="1"' جاهزة للطباعة داخل وسم HTML
*/

if (! function_exists('cms_schema')) {
    /** مخطط الإعدادات القابل للتعديل (resources/cms/settings.php). */
    function cms_schema(): array
    {
        static $schema = null;

        return $schema ??= require resource_path('cms/settings.php');
    }
}

if (! function_exists('site_settings_all')) {
    /** كل الإعدادات الخام (setting_key => setting_value) باستعلام واحد لكل طلب. */
    function site_settings_all(bool $fresh = false): array
    {
        static $memo = null;

        if ($fresh) {
            $memo = null;
        }

        if ($memo !== null) {
            return $memo;
        }

        $memo = [];

        try {
            foreach (DB::table('site_settings')->get(['setting_key', 'setting_value']) as $row) {
                $memo[$row->setting_key] = $row->setting_value;
            }
        } catch (\Throwable $e) {
            $memo = [];
        }

        return $memo;
    }
}

if (! function_exists('setting_decode')) {
    /** يفك ترميز JSON حتى مرتين (يدعم القيم القديمة المرمّزة مرتين). */
    function setting_decode(mixed $raw): mixed
    {
        for ($i = 0; $i < 2 && is_string($raw); $i++) {
            $decoded = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                break;
            }
            $raw = $decoded;
        }

        return $raw;
    }
}

if (! function_exists('is_en')) {
    function is_en(): bool
    {
        return app()->getLocale() === 'en';
    }
}

if (! function_exists('locale_dir')) {
    /** rtl | ltr حسب لغة الطلب الحالية. */
    function locale_dir(): string
    {
        return config('rashfa.locales.' . app()->getLocale() . '.dir', 'rtl');
    }
}

if (! function_exists('ui')) {
    /** نص واجهة ثابت من lang/{locale}.json (المفتاح ui.xxx). */
    function ui(string $key, array $replace = []): string
    {
        return (string) __('ui.' . $key, $replace);
    }
}

if (! function_exists('loc')) {
    /**
     * حقل مترجم من نموذج Eloquent: name → name_en عند اللغة الإنجليزية (وإلا name_ar).
     * إن كانت الترجمة فارغة نرجع للعربية حتى لا يظهر الحقل فارغاً.
     */
    function loc(mixed $model, string $field, ?string $fallback = null): ?string
    {
        if ($model === null) {
            return $fallback;
        }
        if (is_en()) {
            $en = data_get($model, $field . '_en');
            if (filled($en)) {
                return (string) $en;
            }
        }
        $ar = data_get($model, $field . '_ar');

        return filled($ar) ? (string) $ar : $fallback;
    }
}

if (! function_exists('setting_db')) {
    /** قيمة خام مفكوكة من قاعدة البيانات بمفتاح فعلي (بدون ترجمة/افتراضي)، أو null. */
    function setting_db(string $dbKey): mixed
    {
        $all = site_settings_all();

        if (! array_key_exists($dbKey, $all)) {
            return null;
        }

        $value = setting_decode($all[$dbKey]);

        if (is_array($value)) {
            $value = array_key_exists('value', $value) ? $value['value'] : null;
        }

        return ($value === null || $value === '') ? null : $value;
    }
}

if (! function_exists('setting_lang_default')) {
    /** النص الافتراضي للحقل في ملف الترجمة (lang/{locale}.json ← cms.المفتاح)، أو null. */
    function setting_lang_default(string $key, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $id = 'cms.' . $key;
        $line = trans($id, [], $locale);

        return ($line === $id || ! is_string($line) || $line === '') ? null : $line;
    }
}

if (! function_exists('setting_db_key')) {
    /** مفتاح التخزين للغة معيّنة: العربية بلا لاحقة، وغيرها key__en. */
    function setting_db_key(string $key, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'ar' ? $key : $key . '__' . $locale;
    }
}

if (! function_exists('setting')) {
    /**
     * قراءة إعداد نصي حسب لغة الزائر.
     * الأولوية: ما حفظه المدير للغة الحالية ← النص الافتراضي في lang/*.json ← الافتراضي داخل Blade.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        $locale = app()->getLocale();

        $value = setting_db(setting_db_key($key, $locale));
        if ($value !== null) {
            return $value;
        }

        if ($locale !== 'ar' || ! array_key_exists($key, site_settings_all())) {
            $fromLang = setting_lang_default($key, $locale);
            if ($fromLang !== null) {
                return $fromLang;
            }
        }

        // الإنجليزية بلا ترجمة محفوظة ولا ملف: نعرض الأصل العربي بدل الفراغ
        return $locale === 'ar' ? $default : (setting_db($key) ?? $default);
    }
}

if (! function_exists('cms_translatable')) {
    /** هل للحقل نسخة إنجليزية في لوحة التحكم؟ (نصوص فقط، وتُستثنى الحقول التقنية بـ 'i18n' => false). */
    function cms_translatable(array $field): bool
    {
        return in_array($field['type'] ?? '', ['text', 'textarea'], true) && ($field['i18n'] ?? true) !== false;
    }
}

if (! function_exists('media_url')) {
    /** رابط صورة صالح (مسار نسبي، storage، أو رابط كامل) — نقطة واحدة لكل مسارات الصور. */
    function media_url(?string $path, string $default = ''): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            $path = $default;
        }
        if ($path === '') {
            return '';
        }

        if (preg_match('#^(https?:)?//#i', $path)) {
            return $path;
        }

        $relative = '/' . ltrim($path, '/');
        $origin = rtrim((string) config('rashfa.media_base_url'), '/');

        if (str_starts_with($origin, 'https://') && preg_match('#^/(storage|media)/#', $relative)) {
            return $origin . $relative;
        }

        return $relative;
    }
}

if (! function_exists('site_setting')) {
    function site_setting(string $key, mixed $default = null): mixed
    {
        return setting($key, $default);
    }
}

if (! function_exists('setting_image')) {
    /** رابط صورة من إعداد CMS (الصور المرفوعة تُخدَم من /storage، والافتراضية من public/media). */
    function setting_image(string $key, string $default = ''): string
    {
        return media_url((string) setting_db($key), $default);
    }
}

if (! function_exists('font_choices')) {
    function font_choices(): array
    {
        return [
            'plex'  => ['label' => 'IBM Plex (عصري)',        'css' => "'IBM Plex Sans Arabic',sans-serif"],
            'cairo' => ['label' => 'القاهرة (Cairo)',          'css' => "'Cairo','Tajawal',sans-serif"],
            'tajawal' => ['label' => 'تجوّل (Tajawal)',        'css' => "'Tajawal',sans-serif"],
            'kufi'  => ['label' => 'نوتو كوفي (عريض)',        'css' => "'Noto Kufi Arabic',sans-serif"],
            'serif' => ['label' => 'خط بسيط بزوائد',          'css' => "Georgia,'Times New Roman',serif"],
            'sans'  => ['label' => 'خط نظام عادي',            'css' => "system-ui,-apple-system,'Segoe UI',Tahoma,sans-serif"],
        ];
    }
}

if (! function_exists('font_size_choices')) {
    function font_size_choices(): array
    {
        return [14, 16, 18, 20, 24, 28, 32, 40, 48, 56, 64, 72, 80, 96, 120, 150];
    }
}

if (! function_exists('setting_style')) {
    /** تنسيق الحقل المحفوظ (font_size, font_family, color, font_weight, text_align). */
    function setting_style(string $key): array
    {
        $all = site_settings_all();
        $raw = $all[$key . '__style'] ?? null;
        if ($raw === null) {
            return [];
        }

        $style = setting_decode($raw);

        return is_array($style) ? $style : [];
    }
}

if (! function_exists('style_css')) {
    /** يحوّل مصفوفة التنسيق إلى سلسلة CSS آمنة مع !important. */
    function style_css(array $s): string
    {
        $css = [];

        if (isset($s['font_size']) && is_numeric($s['font_size']) && $s['font_size'] >= 10 && $s['font_size'] <= 200) {
            $n = (float) $s['font_size'];
            $vw = round($n / 6.4, 2);
            // حجم ثابت على الشاشات الكبيرة، ويتقلّص تلقائياً على الجوال حتى لا ينكسر التصميم.
            $css[] = 'font-size:min(' . $n . 'px,' . $vw . 'vw) !important';
        }
        if (! empty($s['font_family']) && isset(font_choices()[$s['font_family']])) {
            $css[] = 'font-family:' . font_choices()[$s['font_family']]['css'] . ' !important';
        }
        if (! empty($s['color']) && preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $s['color'])) {
            $css[] = 'color:' . $s['color'] . ' !important';
        }
        if (! empty($s['font_weight']) && in_array((string) $s['font_weight'], ['400', '500', '600', '700', '800'], true)) {
            $css[] = 'font-weight:' . $s['font_weight'] . ' !important';
        }
        if (! empty($s['text_align']) && in_array($s['text_align'], ['right', 'center', 'left'], true)) {
            $css[] = 'text-align:' . $s['text_align'] . ' !important';
        }

        return implode(';', $css);
    }
}

if (! function_exists('setting_style_attr')) {
    /**
     * ' style="..." data-styled="1"' — تنسيق Inline مباشر على الوسم.
     * data-styled يجعل الكلمات الملوّنة داخل العنوان (em / span) ترث نفس التنسيق.
     */
    function setting_style_attr(string $key): HtmlString
    {
        $css = style_css(setting_style($key));

        return new HtmlString($css === '' ? '' : ' style="' . e($css) . '" data-styled="1"');
    }
}

if (! function_exists('title_html')) {
    /**
     * عنوان بتنسيق الموقع: آخر كلمة داخل <em>، وأول كلمة داخل <span> إن طُلب.
     * يكسر السطر تلقائياً في المنتصف، أو عند كل علامة | يكتبها المدير.
     */
    function title_html(?string $text, bool $firstWordSpan = false): HtmlString
    {
        $text = trim((string) $text);
        if ($text === '') {
            return new HtmlString('');
        }

        $lines = array_map(
            fn ($line) => preg_split('/\s+/u', trim($line), -1, PREG_SPLIT_NO_EMPTY),
            explode('|', $text)
        );

        if (count($lines) === 1 && count($lines[0]) >= 2) {
            $words = $lines[0];
            $cut = (int) ceil(count($words) / 2);
            $lines = [array_slice($words, 0, $cut), array_slice($words, $cut)];
        }

        $lines = array_values(array_filter($lines));
        $lastLine = count($lines) - 1;
        $html = [];

        foreach ($lines as $li => $words) {
            $parts = [];
            foreach ($words as $wi => $word) {
                $w = e($word);
                if ($li === $lastLine && $wi === count($words) - 1) {
                    $w = '<em>' . $w . '</em>';
                } elseif ($firstWordSpan && $li === 0 && $wi === 0) {
                    $w = '<span>' . $w . '</span>';
                }
                $parts[] = $w;
            }
            $html[] = implode(' ', $parts);
        }

        return new HtmlString(implode('<br>', $html));
    }
}

if (! function_exists('image_effects_class')) {
    /** يحوّل تأثيرات الصورة المحفوظة (glass / transparent / cutout) إلى أسماء أصناف CSS. */
    function image_effects_class(?array $effects): string
    {
        $allowed = ['glass', 'transparent', 'cutout'];

        return collect($effects ?? [])->intersect($allowed)->map(fn ($e) => 'fx-' . $e)->implode(' ');
    }
}

if (! function_exists('safe_url')) {
    /**
     * رابط آمن للطباعة داخل href: يقبل http/https/mailto/tel والمسارات النسبية والمرساة (#).
     * يضيف https:// تلقائياً إن كُتب الرابط بدون بروتوكول (مثل maps.app.goo.gl/xxxx)، ويرفض javascript: وأمثاله.
     * يرجع '' إذا كان الرابط فارغاً أو غير صالح أو مجرد '#'.
     */
    function safe_url(mixed $url): string
    {
        $url = trim((string) $url);

        if ($url === '' || $url === '#') {
            return '';
        }
        if (preg_match('/[\x00-\x1F\x7F\s]/u', $url)) {
            return '';
        }
        if (preg_match('#^(https?:)?//#i', $url) || preg_match('#^(mailto|tel):#i', $url) || preg_match('#^[/\#]#', $url)) {
            return str_starts_with($url, '//') ? 'https:' . $url : $url;
        }
        if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $url)) {
            return ''; // بروتوكول غير مسموح (javascript:, data: ...)
        }

        return 'https://' . ltrim($url, '/');
    }
}

if (! function_exists('factory_map_url')) {
    /**
     * رابط موقع المصنع على خرائط جوجل — يُضبط من لوحة التحكم (التواصل ← رابط موقع المصنع).
     * يقبل: رابط خرائط جوجل (طويل أو مختصر)، أو إحداثيات "24.7136, 46.6753"، أو عنواناً نصياً للبحث.
     * يرجع '' إذا لم يُضبط (فيُخفى الزر تلقائياً).
     */
    function factory_map_url(): string
    {
        $raw = trim((string) setting('contact.map_url', ''));

        if ($raw === '') {
            return '';
        }

        if (preg_match('/^-?\d{1,3}(?:\.\d+)?\s*,\s*-?\d{1,3}(?:\.\d+)?$/', $raw)) {
            return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(preg_replace('/\s+/', '', $raw));
        }

        $url = safe_url($raw);
        if ($url !== '' && preg_match('#^https?://#i', $url) && str_contains($url, '.')) {
            return $url;
        }

        // نص عادي (عنوان المصنع): نبحث عنه في خرائط جوجل
        if (! preg_match('#^[a-z][a-z0-9+.\-]*:#i', $raw) && ! preg_match('#^[/\#]#', $raw)) {
            return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($raw);
        }

        return '';
    }
}
