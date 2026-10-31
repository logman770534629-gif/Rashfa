<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SiteContentController extends Controller
{
    /** الصور المرفوعة تُحفظ على قرص public (storage/app/public) وتُخدَم من /storage بعد php artisan storage:link */
    private const UPLOAD_DIR = 'rashfa/uploads';
    private const LEGACY_DIR = 'media/rashfa/uploads';

    public function index(?string $group = null): View
    {
        $schema = cms_schema();
        $current = $group ?? array_key_first($schema);
        abort_unless(isset($schema[$current]), 404);

        return view('admin.content.index', compact('schema', 'current'));
    }

    /** يحفظ كل الأقسام دفعة واحدة (الصفحة كلها نموذج واحد بتبويبات). */
    public function update(Request $request): RedirectResponse
    {
        $schema = cms_schema();
        $fields = [];
        foreach ($schema as $group) {
            $fields += $group['fields'];
        }

        $request->validate($this->rules($fields));

        $existing = site_settings_all(true);

        foreach ($fields as $key => $field) {
            $id = $this->id($key);

            // استعادة الافتراضي: تحذف القيمة والتنسيق معاً
            if ($request->boolean("reset.$id")) {
                $this->forget($key, deleteUpload: true);
                $this->forget($key . '__style');
                $this->forget($key . '__en');
                continue;
            }

            // ---- التنسيق: يُحفظ في صف واحد JSON تحت المفتاح (الحقل + __style) ----
            if (! empty($field['styleable'])) {
                if ($request->boolean("style_reset.$id")) {
                    $this->forget($key . '__style');
                } else {
                    $style = $this->cleanStyle((array) $request->input("style.$id", []));
                    if ($style) {
                        $this->put($key . '__style', $style, ($field['label'] ?? $key) . ' (تنسيق)');
                    } else {
                        $this->forget($key . '__style');
                    }
                }
            }

            // ---- الصور ----
            if ($field['type'] === 'image') {
                if ($request->hasFile("img.$id")) {
                    $this->deleteOldUpload($key);
                    $this->put($key, $this->storeImage($request->file("img.$id")), $field['label'] ?? $key);
                }
                continue;
            }

            // ---- النصوص والقيم ----
            if (! $request->has("f.$id")) {
                $this->saveEnglish($request, $key, $field, $id, $existing);
                continue;
            }

            $value = trim((string) $request->input("f.$id"));

            // الروابط: نضيف https:// تلقائياً إن نُسيت، ونرفض javascript: وأمثاله
            if (($field['type'] === 'url') || ! empty($field['normalize_url'])) {
                $value = $this->normalizeUrl($value);
            }

            $default = (string) ($field['default'] ?? '');
            $hasRow = array_key_exists($key, $existing);

            // لا نكتب الافتراضي في القاعدة إلا إذا كان محفوظاً أصلاً (حتى تبقى التحديثات المستقبلية للافتراضي فعّالة)
            if (! $hasRow && $value === $default) {
                $this->saveEnglish($request, $key, $field, $id, $existing);
                continue;
            }

            $this->put($key, $value, $field['label'] ?? $key);

            // ---- النسخة الإنجليزية (حقل مستقل f_en) ----
            $this->saveEnglish($request, $key, $field, $id, $existing);
        }

        // مسح الكاش لتظهر التعديلات فوراً (لا نسمح لفشل الكاش بتعطيل الحفظ)
        try {
            Cache::flush();
        } catch (\Throwable $e) {
            report($e);
        }
        site_settings_all(true);

        $tab = $request->input('tab');

        return redirect()
            ->route('admin.content.index', $tab && isset($schema[$tab]) ? $tab : null)
            ->with('status', 'تم حفظ التغييرات بنجاح. الموقع يعرض المحتوى الجديد الآن.');
    }

    private function rules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $key => $field) {
            $id = $this->id($key);

            $rules["f.$id"] = match ($field['type']) {
                'textarea' => ['nullable', 'string', 'max:5000'],
                'url'      => ['nullable', 'string', 'max:500'],
                'toggle'   => ['nullable', 'in:0,1'],
                'select'   => ['nullable', 'in:' . implode(',', array_keys($field['options'] ?? []))],
                'image'    => ['nullable'],
                'palette'  => ['nullable', 'string', 'max:300'],
                default    => ['nullable', 'string', 'max:1000'],
            };

            if (cms_translatable($field)) {
                $rules["f_en.$id"] = ['nullable', 'string', 'max:' . ($field['type'] === 'textarea' ? 5000 : 1000)];
            }

            if ($field['type'] === 'image') {
                $rules["img.$id"] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'];
            }

            if (! empty($field['styleable'])) {
                $rules["style.$id.font_size"]   = ['nullable', 'numeric', 'between:10,200'];
                $rules["style.$id.font_family"] = ['nullable', 'in:' . implode(',', array_keys(font_choices()))];
                $rules["style.$id.color"]       = ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'];
                $rules["style.$id.font_weight"] = ['nullable', 'in:400,500,600,700,800'];
                $rules["style.$id.text_align"]  = ['nullable', 'in:right,center,left'];
            }
        }

        return $rules;
    }

    /**
     * يوحّد قيمة الرابط: يُبقي http/https/mailto/tel والمسارات والمرساة والإحداثيات والعناوين النصية،
     * ويضيف https:// لما يشبه النطاق (maps.app.goo.gl/xxx)، ويُفرغ أي بروتوكول خطير (javascript:, data:).
     */
    private function normalizeUrl(string $value): string
    {
        if ($value === '' || $value === '#' || preg_match('#^(https?://|mailto:|tel:|/|\#)#i', $value)) {
            return $value;
        }
        if (preg_match('/^-?\d{1,3}(\.\d+)?\s*,\s*-?\d{1,3}(\.\d+)?$/', $value)) {
            return $value; // إحداثيات
        }
        if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $value)) {
            return ''; // بروتوكول غير مسموح
        }
        if (! preg_match('/\s/u', $value) && str_contains($value, '.')) {
            return 'https://' . ltrim($value, '/');
        }

        return $value; // عنوان نصي: يُبحث عنه في خرائط جوجل
    }

    /** يُبقي المفاتيح المعروفة وغير الفارغة فقط. */
    private function cleanStyle(array $input): array
    {
        $style = [];
        foreach (['font_size', 'font_family', 'color', 'font_weight', 'text_align'] as $k) {
            $v = $input[$k] ?? null;
            if ($v !== null && $v !== '') {
                $style[$k] = (string) $v;
            }
        }

        return $style;
    }

    private function id(string $key): string
    {
        return str_replace('.', '__', $key);
    }

    private function storeImage($file): string
    {
        $name = Str::uuid() . '.' . ($file->guessExtension() ?: 'jpg');
        $file->storeAs(self::UPLOAD_DIR, $name, 'public');

        // المسار النسبي المخزّن: storage/rashfa/uploads/xxx.jpg  (يُخدَم عبر رابط storage:link)
        return 'storage/' . self::UPLOAD_DIR . '/' . $name;
    }

    private function deleteOldUpload(string $key): void
    {
        $old = (string) setting_db($key);

        if (Str::startsWith($old, 'storage/' . self::UPLOAD_DIR . '/')) {
            Storage::disk('public')->delete(Str::after($old, 'storage/'));
        } elseif (Str::startsWith($old, self::LEGACY_DIR . '/')) {
            $path = public_path($old); // رفعات قديمة قبل الانتقال إلى storage
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    /** يحفظ/يحذف النسخة الإنجليزية key__en. القيمة المطابقة للافتراضي في lang/en.json لا تُخزَّن (تبقى التحديثات الافتراضية فعّالة). */
    private function saveEnglish(Request $request, string $key, array $field, string $id, array $existing): void
    {
        if (! cms_translatable($field) || ! $request->has("f_en.$id")) {
            return;
        }

        $enKey = $key . '__en';
        $value = trim((string) $request->input("f_en.$id"));
        $langDefault = (string) (setting_lang_default($key, 'en') ?? '');

        if ($value === '' || ($value === $langDefault && ! array_key_exists($enKey, $existing))) {
            if ($value === '') {
                $this->forget($enKey);
            }

            return;
        }

        $this->put($enKey, $value, ($field['label'] ?? $key) . ' (EN)');
    }

    /** القيمة تُخزَّن كـ JSON صالح: نص عادي => "نص"، وتنسيق => {"color":"#fff"}. */
    private function put(string $key, mixed $value, string $label): void
    {
        $data = [
            'setting_value' => json_encode($value, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('site_settings', 'description_ar')) {
            $data['description_ar'] = mb_substr($label, 0, 300);
        }
        if (Schema::hasColumn('site_settings', 'is_public')) {
            $data['is_public'] = 1;
        }

        $exists = DB::table('site_settings')->where('setting_key', $key)->exists();

        if ($exists) {
            DB::table('site_settings')->where('setting_key', $key)->update($data);

            return;
        }

        $data['setting_key'] = $key;
        $data['created_at'] = now();
        DB::table('site_settings')->insert($data);
    }

    private function forget(string $key, bool $deleteUpload = false): void
    {
        if ($deleteUpload) {
            $this->deleteOldUpload($key);
        }
        DB::table('site_settings')->where('setting_key', $key)->delete();
    }
}
