<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Database\Seeder;

/** نكهات سن بير الحقيقية: طبيعية 100% وخالية من المواد الحافظة. آمن للتشغيل أكثر من مرة. */
class SunBirdFlavorSeeder extends Seeder
{
    public function run(): void
    {
        $flavors = [
            ['برتقال طبيعي', 'Natural Orange', 'natural-orange', '#FF7A1A', 'orange', 'عصير برتقال طبيعي 100%، غني بفيتامين C، خالٍ من المواد الحافظة والألوان الصناعية.'],
            ['تفاح صافي', 'Pure Apple', 'pure-apple', '#9BC53D', 'apple', 'تفاح صافي بحلاوته الطبيعية 100%، خالٍ تماماً من المواد الحافظة.'],
            ['مانجو غني', 'Rich Mango', 'rich-mango', '#FFB300', 'mango', 'مانجو غنية بقوام مخملي، طبيعية 100% وخالية من المواد الحافظة.'],
            ['جوافة منعشة', 'Fresh Guava', 'fresh-guava', '#F06A8D', 'guava', 'جوافة منعشة برائحة زكية، طبيعية 100% وبلا مواد حافظة.'],
            ['مزيج الفواكه المشكلة', 'Mixed Fruits', 'mixed-fruits', '#E8344E', 'mixed', 'مزيج من أجود الفواكه، طبيعي 100% وخالٍ من المواد الحافظة والألوان الصناعية.'],
        ];

        foreach ($flavors as $i => [$ar, $en, $slug, $color, $tag, $desc]) {
            Product::updateOrCreate(['slug' => $slug], [
                'name_ar' => $ar, 'name_en' => $en,
                'short_desc_ar' => $desc, 'short_desc_en' => $en . ' juice, 100% natural, no preservatives.',
                'sip_story_ar' => 'انتعاش الطبيعة في كل رشفة.',
                'base_color' => '#0E3B2E', 'accent_color' => $color,
                'nutrition_json' => ['فيتامينات ومعادن' => 'غني'], 'tags_json' => ['طبيعي 100%', 'بدون مواد حافظة'],
                'sort_order' => $i + 1, 'is_published' => true, 'is_featured' => $i === 0, 'is_active' => true,
            ]);
        }

        foreach ([['برتقال', 'Orange', 'orange'], ['تفاح', 'Apple', 'apple'], ['مانجو', 'Mango', 'mango'], ['جوافة', 'Guava', 'guava']] as [$ar, $en, $slug]) {
            Ingredient::updateOrCreate(['slug' => $slug], ['name_ar' => $ar, 'name_en' => $en, 'description_ar' => 'فاكهة طبيعية 100% بلا مواد حافظة.', 'is_active' => true]);
        }
    }
}
