<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $isMariaDb = DB::connection()->getDriverName() === 'mysql' && str_contains(strtolower((string) DB::selectOne('select version() as version')->version), 'mariadb');
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name_ar', 120); $table->string('name_en', 120); $table->string('slug', 150)->unique();
            $table->string('description_ar', 500)->nullable(); $table->string('description_en', 500)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0); $table->boolean('is_active')->default(true);
            $table->timestamps(); $table->index(['is_active', 'sort_order']);
        });
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id'); $table->unsignedInteger('category_id'); $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('name_ar', 160); $table->string('name_en', 160); $table->string('slug', 180)->unique();
            $table->string('short_desc_ar', 500); $table->string('short_desc_en', 500); $table->text('long_desc_ar')->nullable(); $table->text('long_desc_en')->nullable();
            $table->text('sip_story_ar')->nullable(); $table->text('sip_story_en')->nullable(); $table->char('base_color', 7)->default('#0E3B2E'); $table->char('accent_color', 7)->default('#C6F135');
            $table->json('nutrition_json'); $table->json('tags_json'); $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(false); $table->boolean('is_featured')->default(false); $table->boolean('is_active')->default(true); $table->timestamps();
            $table->index(['category_id', 'is_active', 'is_published', 'sort_order']); $table->index(['is_active', 'is_published', 'is_featured', 'sort_order']); $table->fullText(['name_ar', 'short_desc_ar', 'long_desc_ar']);
        });
        Schema::create('media_library', function (Blueprint $table) {
            $table->increments('id'); $table->string('file_name'); $table->string('storage_path', 1024); $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size_bytes')->default(0); $table->unsignedInteger('width_px')->nullable(); $table->unsignedInteger('height_px')->nullable(); $table->char('checksum_sha256', 64)->nullable(); $table->timestamps(); $table->index('mime_type');
        });
        if (DB::connection()->getDriverName() === 'mysql') { DB::statement('CREATE UNIQUE INDEX uq_media_storage_path ON media_library (storage_path(255))'); }
        Schema::create('product_images', function (Blueprint $table) use ($isMariaDb) {
            $table->increments('id'); $table->unsignedInteger('product_id'); $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete()->cascadeOnUpdate(); $table->unsignedInteger('media_id'); $table->foreign('media_id')->references('id')->on('media_library')->restrictOnDelete()->cascadeOnUpdate();
            $table->enum('image_type', ['hero', 'gallery', 'thumbnail', 'texture_3d', 'banner'])->default('gallery'); $table->string('alt_ar', 300); $table->boolean('is_primary')->default(false); $table->unsignedSmallInteger('sort_order')->default(0);
            if ($isMariaDb) { $table->unsignedInteger('primary_product_id')->nullable(); } else { $table->unsignedInteger('primary_product_id')->nullable()->storedAs('IF(is_primary = 1, product_id, NULL)'); } $table->timestamps();
            $table->unique('primary_product_id', 'uq_product_images_single_primary'); $table->unique(['product_id', 'media_id'], 'uq_product_images_media'); $table->index(['product_id', 'image_type', 'sort_order']);
        });
        Schema::create('ingredients', function (Blueprint $table) {
            $table->increments('id'); $table->string('name_ar', 120); $table->string('name_en', 120); $table->string('slug', 150)->unique(); $table->string('description_ar', 500)->nullable(); $table->string('description_en', 500)->nullable(); $table->boolean('is_active')->default(true); $table->timestamps(); $table->index(['is_active', 'name_ar']);
        });
        Schema::create('product_ingredients', function (Blueprint $table) {
            $table->increments('id'); $table->unsignedInteger('product_id'); $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete()->cascadeOnUpdate(); $table->unsignedInteger('ingredient_id'); $table->foreign('ingredient_id')->references('id')->on('ingredients')->restrictOnDelete()->cascadeOnUpdate(); $table->decimal('percentage', 5, 2); $table->unsignedSmallInteger('sort_order')->default(0); $table->timestamps(); $table->unique(['product_id','ingredient_id'], 'uq_product_ingredient'); $table->index(['ingredient_id','product_id']);
        });
        Schema::create('product_variants', function (Blueprint $table) {
            $table->increments('id'); $table->unsignedInteger('product_id'); $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete()->cascadeOnUpdate(); $table->unsignedSmallInteger('size_ml'); $table->string('label_ar', 80); $table->string('label_en', 80); $table->decimal('price', 10, 2); $table->decimal('compare_at_price', 10, 2)->nullable(); $table->string('sku', 80)->unique(); $table->unsignedInteger('stock_quantity')->default(0); $table->boolean('is_active')->default(true); $table->unsignedSmallInteger('sort_order')->default(0); $table->timestamps(); $table->unique(['product_id','size_ml'], 'uq_product_variants_size'); $table->index(['product_id','is_active','sort_order']);
        });
        Schema::create('product_story_phases', function (Blueprint $table) {
            $table->increments('id'); $table->unsignedInteger('product_id'); $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete()->cascadeOnUpdate(); $table->unsignedInteger('media_id')->nullable(); $table->foreign('media_id')->references('id')->on('media_library')->nullOnDelete()->cascadeOnUpdate(); $table->enum('phase_key', ['harvest','press','sip']); $table->string('title_ar', 160); $table->string('title_en', 160); $table->text('body_ar'); $table->text('body_en'); $table->char('background_color', 7)->default('#F5EFE6'); $table->unsignedTinyInteger('phase_order'); $table->timestamps(); $table->unique(['product_id','phase_order'], 'uq_story_product_order'); $table->unique(['product_id','phase_key'], 'uq_story_product_phase'); $table->index('media_id');
        });
        Schema::create('admins', function (Blueprint $table) {
            $table->increments('id'); $table->string('email', 254)->unique(); $table->string('full_name', 160); $table->string('password_hash', 255); $table->enum('role', ['super_admin','editor','viewer'])->default('viewer'); $table->boolean('is_active')->default(true); $table->unsignedSmallInteger('failed_attempts')->default(0); $table->dateTime('locked_until')->nullable(); $table->dateTime('last_login_at')->nullable(); $table->timestamps(); $table->index(['is_active','role']); $table->index('locked_until');
        });
        Schema::create('site_settings', function (Blueprint $table) {
            $table->increments('id'); $table->string('setting_key', 120)->unique(); $table->json('setting_value'); $table->string('description_ar', 300)->nullable(); $table->boolean('is_public')->default(false); $table->timestamps(); $table->index('is_public');
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE products ADD CONSTRAINT chk_products_base_color CHECK (base_color REGEXP '^#[0-9A-Fa-f]{6}$')");
            DB::statement("ALTER TABLE products ADD CONSTRAINT chk_products_accent_color CHECK (accent_color REGEXP '^#[0-9A-Fa-f]{6}$')");
            DB::statement("ALTER TABLE product_story_phases ADD CONSTRAINT chk_story_phase_order CHECK (phase_order BETWEEN 1 AND 3)");
            DB::statement("ALTER TABLE product_story_phases ADD CONSTRAINT chk_story_background CHECK (background_color REGEXP '^#[0-9A-Fa-f]{6}$')");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings'); Schema::dropIfExists('admins'); Schema::dropIfExists('product_story_phases'); Schema::dropIfExists('product_variants'); Schema::dropIfExists('product_ingredients'); Schema::dropIfExists('ingredients'); Schema::dropIfExists('product_images'); Schema::dropIfExists('media_library'); Schema::dropIfExists('products'); Schema::dropIfExists('categories');
    }
};
