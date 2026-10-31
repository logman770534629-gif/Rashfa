<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * يسجّل إعداد «رابط موقع المصنع (خرائط جوجل)» في جدول site_settings (contact.map_url).
 * القيمة تبدأ فارغة (زر الموقع يبقى مخفياً حتى تُدخل الرابط من لوحة التحكم: المحتوى ← التواصل).
 * آمنة للتشغيل أكثر من مرة: لا تلمس أي قيمة موجودة.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        if (! DB::table('site_settings')->where('setting_key', 'contact.map_url')->exists()) {
            $row = [
                'setting_key' => 'contact.map_url',
                'setting_value' => json_encode('', JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('site_settings', 'description_ar')) {
                $row['description_ar'] = 'رابط موقع المصنع (خرائط جوجل)';
            }
            if (Schema::hasColumn('site_settings', 'is_public')) {
                $row['is_public'] = 1;
            }
            DB::table('site_settings')->insert($row);
        }

        try { Cache::flush(); } catch (\Throwable $e) { report($e); }
    }

    public function down(): void
    {
        if (Schema::hasTable('site_settings')) {
            DB::table('site_settings')->where('setting_key', 'contact.map_url')->where('setting_value', json_encode(''))->delete();
        }
    }
};
