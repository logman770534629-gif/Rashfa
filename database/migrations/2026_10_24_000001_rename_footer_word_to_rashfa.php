<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * الكلمة العملاقة في الفوتر: "RASHFA" → "Rashfa".
 * تحدّث القيمة فقط إن كانت محفوظة في قاعدة البيانات بالصيغة القديمة (لا تمسّ أي نص خصصه المدير).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        $rows = DB::table('site_settings')
            ->where('setting_key', 'like', 'footer_word_front%')
            ->get(['id', 'setting_key', 'setting_value']);

        foreach ($rows as $row) {
            $raw = $row->setting_value;
            for ($i = 0; $i < 2 && is_string($raw); $i++) {
                $decoded = json_decode($raw, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    break;
                }
                $raw = $decoded;
            }
            $value = is_array($raw) ? ($raw['value'] ?? null) : $raw;

            if (is_string($value) && trim($value) === 'RASHFA') {
                DB::table('site_settings')->where('id', $row->id)->update([
                    'setting_value' => json_encode(is_array($raw) ? array_merge($raw, ['value' => 'Rashfa']) : 'Rashfa', JSON_UNESCAPED_UNICODE),
                ]);
            }
        }

        try { Cache::flush(); } catch (\Throwable $e) { report($e); }
    }

    public function down(): void
    {
        // لا رجوع: الصيغة الجديدة هي المعتمدة.
    }
};
