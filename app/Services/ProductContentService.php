<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductStoryPhase;
use App\Models\ProductVariant;
use App\Support\UniqueSlug;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ProductContentService
{
    public function __construct(private readonly MediaUploader $uploader) {}

    public function save(array $data, ?Product $product = null): Product
    {
        $image = $data['image'] ?? null;
        $effects = array_values($data['image_effects'] ?? []);
        unset($data['image'], $data['image_effects']);

        // الرابط المختصر (slug) لا يظهر للمستخدم: يُولَّد تلقائياً عند الإنشاء، ويبقى كما هو عند التعديل.
        if ($product === null || blank($product->slug)) {
            $data['slug'] = UniqueSlug::make(Product::class, [$data['name_en'] ?? null, $data['name_ar'] ?? null], $product?->id, 'flavor');
        } else {
            unset($data['slug']);
        }

        $data['tags_json'] = $this->decodeJson($data['tags_json'] ?? null, []);

        // زر "إظهار في الموقع" يضبط الحقلين معاً (النكهة تظهر للزوار فقط إذا كان الاثنان مفعّلين).
        $visible = (bool) ($data['is_visible'] ?? false);
        unset($data['is_visible']);
        $data['is_active'] = $visible;
        $data['is_published'] = $visible;
        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);

        // الترتيب كما أدخله المدير تماماً؛ إن تُرك فارغاً تذهب النكهة إلى النهاية.
        $wanted = isset($data['sort_order']) && $data['sort_order'] !== '' ? (int) $data['sort_order'] : null;
        unset($data['sort_order']);

        return DB::transaction(function () use ($data, $product, $wanted, $image, $effects) {
            $data['sort_order'] = $wanted ?? ((int) Product::max('sort_order') + 1);
            $product = $product ? tap($product)->update($data) : Product::create($data);

            $this->normalizeOrder($product, $wanted !== null);

            if ($image instanceof UploadedFile) {
                $this->attachPrimaryImage($product, $image, $effects);
            }

            return $product->refresh();
        });
    }

    /**
     * حذف نهائي: يحذف صور النكهة ومراحل قصتها وأحجامها ومكوّناتها المرتبطة ثم النكهة نفسها.
     * ملفات المكتبة (media_library) تبقى سليمة لأنها قد تكون مستخدمة في أماكن أخرى.
     */
    public function forceDelete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            ProductImage::where('product_id', $product->id)->delete();
            ProductStoryPhase::where('product_id', $product->id)->delete();
            ProductVariant::where('product_id', $product->id)->delete();
            DB::table('product_ingredients')->where('product_id', $product->id)->delete();
            $product->delete();

            $this->renumber();
        });
    }

    /** ترقيم النكهات 1..N بلا فجوات بعد الحذف. */
    private function renumber(): void
    {
        foreach (Product::query()->orderBy('sort_order')->orderBy('id')->get(['id', 'sort_order']) as $i => $p) {
            if ((int) $p->sort_order !== $i + 1) {
                Product::whereKey($p->id)->update(['sort_order' => $i + 1]);
            }
        }
    }

    /**
     * يعيد ترقيم كل النكهات 1..N بلا فجوات ولا تكرار.
     * عند التعادل تأخذ النكهة التي حُفظت الآن الموضع المطلوب (أي إدخال "1" يضعها أولاً فعلاً).
     */
    private function normalizeOrder(Product $saved, bool $savedWins): void
    {
        $all = Product::query()->orderBy('sort_order')->orderBy('id')->get(['id', 'sort_order']);
        $ordered = $all->sortBy(fn ($p) => [$p->sort_order, ($savedWins && $p->id === $saved->id) ? 0 : 1, $p->id])->values();

        foreach ($ordered as $i => $p) {
            if ((int) $p->sort_order !== $i + 1) {
                Product::whereKey($p->id)->update(['sort_order' => $i + 1]);
            }
        }
    }

    private function attachPrimaryImage(Product $product, UploadedFile $file, array $effects): void
    {
        $media = $this->uploader->store($file);
        ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
        ProductImage::create([
            'product_id' => $product->id,
            'media_id' => $media->id,
            'image_type' => 'hero',
            'alt_ar' => $product->name_ar,
            'is_primary' => true,
            'sort_order' => ((int) $product->images()->max('sort_order')) + 1,
            'effects_json' => $effects,
        ]);
    }

    private function decodeJson(?string $value, array $fallback): array
    {
        if (blank($value)) return $fallback;
        $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        return is_array($decoded) ? $decoded : $fallback;
    }
}
