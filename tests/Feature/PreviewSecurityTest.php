<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PreviewSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('t', 32)),
            'rashfa.preview_read_only' => true,
            'cache.default' => 'array',
            'session.driver' => 'array',
            'session.secure' => false,
            // An unsupported connector guarantees a regression cannot contact
            // any real database, even when a developer has a private .env.
            'database.default' => 'preview_forbidden',
            'database.connections.preview_forbidden' => ['driver' => 'preview_forbidden'],
        ]);

        // None of these regression tests may touch any database.
        DB::listen(function (QueryExecuted $query): void {
            $this->fail('Unexpected database query during preview security test.');
        });
    }

    public function test_guest_can_load_login_without_a_redirect_loop_or_database_access(): void
    {
        $this->get('/secret-portal-admin-99x')->assertOk()->assertSee('password');
    }

    public function test_guest_cannot_reach_admin_dashboard_even_with_legacy_open_flag(): void
    {
        config(['rashfa.admin_open' => true]);
        $this->get('/secret-portal-admin-99x/dashboard')
            ->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_preview_rejects_catalog_and_media_writes_before_controllers_run(): void
    {
        foreach (['/secret-portal-admin-99x/products', '/secret-portal-admin-99x/media', '/secret-portal-admin-99x/settings'] as $path) {
            $this->post($path)->assertForbidden();
        }
        $this->put('/secret-portal-admin-99x/content')->assertForbidden();
    }

    public function test_viewer_cannot_mutate_content_when_writes_are_explicitly_enabled(): void
    {
        config(['rashfa.preview_read_only' => false]);
        $admin = new Admin;
        $admin->forceFill(['id' => 999, 'role' => 'viewer', 'is_active' => true]);
        $this->actingAs($admin, 'admin')->post('/secret-portal-admin-99x/media')->assertForbidden();
    }

    public function test_logout_clears_authentication_without_database_writes(): void
    {
        $admin = new Admin;
        $admin->forceFill(['id' => 999, 'role' => 'viewer', 'is_active' => true]);
        $this->actingAs($admin, 'admin')->post('/secret-portal-admin-99x/logout')
            ->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_login_rate_limit_is_enforced_without_database_access(): void
    {
        // Exercise the real route middleware without authenticating or querying.
        Route::getRoutes()->getByName('admin.login.attempt')->uses(fn () => response('limited-test'));
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/secret-portal-admin-99x', ['email' => 'test@example.invalid'])->assertOk();
        }
        $this->post('/secret-portal-admin-99x', ['email' => 'test@example.invalid'])->assertStatus(429);
    }

    public function test_language_switch_returns_json_without_database_access(): void
    {
        $this->getJson('/lang/en')->assertOk()->assertJson(['locale' => 'en', 'dir' => 'ltr']);
    }

    public function test_existing_media_can_use_an_https_origin_without_rewriting_database_values(): void
    {
        config(['rashfa.media_base_url' => 'https://media.example.invalid']);
        $this->assertSame('https://media.example.invalid/storage/rashfa/image.webp', media_url('/storage/rashfa/image.webp'));
        $this->assertSame('https://other.example.invalid/image.webp', media_url('https://other.example.invalid/image.webp'));
    }
}
