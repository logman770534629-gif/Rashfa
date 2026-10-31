<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\SiteContentController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\StoryPhaseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use App\Http\Middleware\ForceArabicLocale;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::middleware(SetLocale::class)->group(function () {
    Route::get('/', HomeController::class)->name('home');
    Route::get('/lang/{locale}', LanguageController::class)->whereIn('locale', ['ar', 'en'])->name('lang.switch');
});

Route::prefix('secret-portal-admin-99x')->as('admin.')->middleware(ForceArabicLocale::class)->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/', [AuthController::class, 'login'])->middleware('throttle:admin-login')->name('login.attempt');
});

Route::prefix('secret-portal-admin-99x')->as('admin.')->middleware(['admin.active', ForceArabicLocale::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('products', ProductController::class)->except('show');
    Route::resource('ingredients', IngredientController::class)->except(['show', 'create']);
    Route::delete('products/{product}/force', [ProductController::class, 'forceDestroy'])->name('products.force-destroy');
    Route::delete('ingredients/{ingredient}/force', [IngredientController::class, 'forceDestroy'])->name('ingredients.force-destroy');

    Route::get('products/{product}/images', [ProductImageController::class, 'index'])->name('products.images.index');
    Route::post('products/{product}/images', [ProductImageController::class, 'store'])->name('products.images.store');
    Route::patch('products/{product}/images/{image}/effects', [ProductImageController::class, 'effects'])->name('products.images.effects');
    Route::patch('products/{product}/images/{image}/primary', [ProductImageController::class, 'primary'])->name('products.images.primary');
    Route::delete('products/{product}/images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');

    Route::get('products/{product}/story-phases', [StoryPhaseController::class, 'index'])->name('products.story-phases.index');
    Route::post('products/{product}/story-phases', [StoryPhaseController::class, 'store'])->name('products.story-phases.store');
    Route::put('products/{product}/story-phases/{storyPhase}', [StoryPhaseController::class, 'update'])->name('products.story-phases.update');
    Route::delete('products/{product}/story-phases/{storyPhase}', [StoryPhaseController::class, 'destroy'])->name('products.story-phases.destroy');

    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::post('media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

    Route::get('content/{group?}', [SiteContentController::class, 'index'])->name('content.index');
    Route::put('content', [SiteContentController::class, 'update'])->name('content.update');

    Route::get('settings', [SiteSettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SiteSettingController::class, 'store'])->name('settings.store');
    Route::put('settings/{setting}', [SiteSettingController::class, 'update'])->name('settings.update');
});
