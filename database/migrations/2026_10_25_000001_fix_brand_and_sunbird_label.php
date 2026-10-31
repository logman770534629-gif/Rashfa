<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - الكلمة العملاقة في الفوتر: Rashfa/RASHFA → رشة (عربي) و Rasha (إنجليزي).
 * - مسمى القسم: "قصة سن بيرد" / "Sun Bird Story" → "نبذة عن سن بيرد" / "About Sun Bird".
 * تُحدَّث فقط القيم المحفوظة بالصيغة القديمة نفسها (لا تُمَسّ نصوص خصصها المدير).
 */
return new class extends Migration
{
    private array $labels = [
        'قصة سن بيرد' => 'نبذة عن سن بيرد',
        'Sun Bird Story' => 'About Sun Bird',
        'The Sun Bird Story' => 'About Sun Bird',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        foreach (DB::table('site_settings')->get(['id', 'setting_key', 'setting_value']) as $row) {
            $raw = $row->setting_value;
            for ($i = 0; $i < 2 && is_string($raw); $i++) {
                $decoded = json_decode($raw, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    break;
                }
                $raw = $decoded;
            }

            $wrapped = is_array($raw) && array_key_exists('value', $raw);
            $value = $wrapped ? $raw['value'] : $raw;
            if (! is_string($value)) {
                continue;
            }

            $trim = trim($value);
            $isEn = str_ends_with($row->setting_key, '__en');
            $new = null;

            if (str_starts_with($row->setting_key, 'footer_word_front') && in_array($trim, ['RASHFA', 'Rashfa'], true)) {
                $new = $isEn ? 'Rasha' : 'رشة';
            } elseif (isset($this->labels[$trim])) {
                $new = $this->labels[$trim];
            } elseif ($isEn && str_contains($value, 'Rashfa')) {
                $new = str_replace('Rashfa', 'Rasha', $value);
            }

            if ($new === null || $new === $value) {
                continue;
            }

            DB::table('site_settings')->where('id', $row->id)->update([
                'setting_value' => json_encode($wrapped ? array_merge($raw, ['value' => $new]) : $new, JSON_UNESCAPED_UNICODE),
            ]);
        }

        try { Cache::flush(); } catch (\Throwable $e) { report($e); }
    }

    public function down(): void
    {
        // لا رجوع.
    }
};
