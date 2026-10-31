<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * يولّد slug فريداً تلقائياً (لا يراه المستخدم في لوحة التحكم).
 * الأساس: الاسم الإنجليزي ثم العربي؛ وعند التكرار يُضاف رقم عشوائي.
 */
class UniqueSlug
{
    /**
     * @param class-string<Model> $modelClass
     * @param array<int, ?string> $sources    مصادر الاسم بالترتيب (الإنجليزي أولاً)
     */
    public static function make(string $modelClass, array $sources, ?int $ignoreId = null, string $fallback = 'item'): string
    {
        $base = '';
        foreach ($sources as $source) {
            $base = Str::slug((string) $source);
            if ($base !== '') {
                break;
            }
        }
        $base = rtrim(Str::limit($base !== '' ? $base : $fallback, 120, ''), '-') ?: $fallback;

        $taken = static fn (string $slug): bool => $modelClass::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists();

        $slug = $base;
        for ($i = 0; $taken($slug); $i++) {
            $slug = $i < 20
                ? $base . '-' . random_int(1000, 99999)
                : $base . '-' . Str::lower(Str::random(10));
        }

        return $slug;
    }
}
