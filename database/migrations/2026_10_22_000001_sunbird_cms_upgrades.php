<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // صورة وتأثيرات لكل مكوّن
        Schema::table('ingredients', function (Blueprint $table) {
            if (! Schema::hasColumn('ingredients', 'image_media_id')) {
                $table->unsignedInteger('image_media_id')->nullable()->after('slug');
                $table->foreign('image_media_id')->references('id')->on('media_library')->nullOnDelete();
            }
            if (! Schema::hasColumn('ingredients', 'image_effects')) {
                $table->json('image_effects')->nullable()->after('image_media_id');
            }
        });

        // تأثيرات بصرية لصور المنتج (إطار زجاجي / خلفية شفافة / صورة مقطوعة)
        Schema::table('product_images', function (Blueprint $table) {
            if (! Schema::hasColumn('product_images', 'effects_json')) {
                $table->json('effects_json')->nullable()->after('image_type');
            }
        });

        // التصنيف لم يعد إلزامياً بعد إلغاء قسم التصنيفات من لوحة التحكم
        if (Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedInteger('category_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            if (Schema::hasColumn('ingredients', 'image_media_id')) {
                $table->dropForeign(['image_media_id']);
                $table->dropColumn('image_media_id');
            }
            if (Schema::hasColumn('ingredients', 'image_effects')) {
                $table->dropColumn('image_effects');
            }
        });

        Schema::table('product_images', function (Blueprint $table) {
            if (Schema::hasColumn('product_images', 'effects_json')) {
                $table->dropColumn('effects_json');
            }
        });
    }
};
