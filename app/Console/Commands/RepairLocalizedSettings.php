<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * يصلح بيانات site_settings التي تلوّثت عندما كانت لوحة التحكم تعمل بلغة en (APP_LOCALE=en في .env):
 * نص إنجليزي محفوظ في المفتاح العربي → يُنقل إلى key__en ويُحذف من المفتاح العربي (فيعود النص العربي الافتراضي).
 *
 * التشغيل:  php artisan rashfa:repair-i18n          (معاينة فقط)
 *           php artisan rashfa:repair-i18n --apply  (تنفيذ)
 */
class RepairLocalizedSettings extends Command
{
    protected $signature = 'rashfa:repair-i18n {--apply : نفّذ الإصلاح فعلاً}';

    protected $description = 'ينقل النصوص الإنجليزية المحفوظ خطأً في الحقول العربية إلى حقولها الإنجليزية';

    public function handle(): int
    {
        $arabic = '/[\x{0600}-\x{06FF}]/u';
        $rows = DB::table('site_settings')->get(['setting_key', 'setting_value'])->keyBy('setting_key');
        $fixed = 0;

        foreach (cms_schema() as $group) {
            foreach ($group['fields'] as $key => $field) {
                if (! cms_translatable($field) || ! isset($rows[$key])) {
                    continue;
                }

                $default = (string) ($field['default'] ?? '');
                $value = setting_decode($rows[$key]->setting_value);
                if (is_array($value)) {
                    $value = $value['value'] ?? null;
                }
                $value = trim((string) $value);

                // الافتراضي عربي، والمحفوظ بلا أي حرف عربي => نص إنجليزي في المكان الخطأ
                if ($value === '' || ! preg_match($arabic, $default) || preg_match($arabic, $value)) {
                    continue;
                }

                $this->line(sprintf('%s: "%s"', $key, mb_strimwidth($value, 0, 70, '…')));
                $fixed++;

                if (! $this->option('apply')) {
                    continue;
                }

                if (! isset($rows[$key . '__en'])) {
                    DB::table('site_settings')->insert([
                        'setting_key' => $key . '__en',
                        'setting_value' => json_encode($value, JSON_UNESCAPED_UNICODE),
                        'description_ar' => mb_substr(($field['label'] ?? $key) . ' (EN)', 0, 300),
                        'is_public' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                DB::table('site_settings')->where('setting_key', $key)->delete();
            }
        }

        if ($this->option('apply')) {
            try { Cache::flush(); } catch (\Throwable $e) { report($e); }
            $this->info("تم إصلاح {$fixed} حقل.");
        } else {
            $this->info("وُجد {$fixed} حقل يحتاج إصلاحاً. أعد التشغيل مع --apply للتنفيذ.");
        }

        return self::SUCCESS;
    }
}
