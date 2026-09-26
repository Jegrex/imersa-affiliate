<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FrontendPreviewTest extends TestCase
{
    public static function pages(): array
    {
        return array_map(fn ($path) => [$path], [
            '/preview', '/preview/catalog', '/preview/products/macbook-air-m2',
            '/preview/admin/login', '/preview/admin/dashboard', '/preview/admin/products',
            '/preview/admin/products/create', '/preview/admin/products/1/edit',
            '/preview/admin/products/1/reviews',
            '/preview/admin/categories', '/preview/admin/categories/create',
            '/preview/admin/categories/1/edit', '/preview/admin/analytics', '/preview/admin/imports',
        ]);
    }

    #[DataProvider('pages')]
    public function test_preview_pages_render_without_a_database(string $path): void
    {
        $this->get($path)->assertOk()->assertSee('Pratinjau frontend')->assertSee('belum terhubung ke backend');
    }

    public function test_catalog_searches_names_and_hides_drafts(): void
    {
        $this->get('/preview/catalog?q=macbook')->assertOk()->assertSee('MacBook Air M2')->assertDontSee('ThinkPad X1 Carbon');
        $this->get('/preview/catalog?q=pratinjau')->assertOk()->assertSee('Belum ada produk yang cocok');
        $this->get('/preview/products/rog-zephyrus-g14-oled')->assertNotFound();
        $this->get('/preview/catalog?category=audio')->assertOk()->assertSee('Live Pro 2 TWS')->assertDontSee('MacBook Air M2');
        $this->get('/preview/catalog?category=laptop-pc')->assertOk()->assertSee('MacBook Air M2')->assertDontSee('Galaxy S23 Ultra');
    }

    public function test_nullable_data_and_unavailable_affiliate_do_not_become_zero_or_links(): void
    {
        $this->get('/preview/products/galaxy-a55-5g')->assertOk()->assertSee('Harga tidak tersedia')->assertSee('Tautan pembelian belum tersedia')->assertDontSee('Rp 0');
        $this->get('/preview/products/live-pro-2-tws')->assertOk()->assertSee('Rating belum tersedia');
    }

    public function test_hidden_reviews_are_not_published(): void
    {
        $this->get('/preview/products/macbook-air-m2')->assertOk()->assertSee('Pengguna contoh A')->assertDontSee('Pengguna contoh B');
    }

    public function test_fixture_actions_never_persist_or_redirect_externally(): void
    {
        $this->post('/preview/admin/products/create', ['name' => 'Tidak tersimpan'])->assertStatus(405);
        $this->post('/preview/admin/products/1/affiliate', ['affiliate_url' => 'https://example.test'])->assertStatus(405);
        $this->get('/preview/products/macbook-air-m2')->assertOk()->assertDontSee('href="https://shopee', false);
    }

    public function test_analytics_respects_period_and_does_not_invent_conversions(): void
    {
        $this->get('/preview/admin/analytics?from=2025-01-01&to=2025-01-31')->assertOk()->assertSee('Belum ada klik pada periode ini');
        $this->get('/preview/admin/analytics?from=2025-01-01&to=2026-09-24')->assertOk()->assertSee('tidak boleh melebihi 366 hari');
        $this->get('/preview/admin/analytics')->assertOk()->assertDontSee('CTR')->assertDontSee('Konversi rujukan');
    }

    public function test_untrusted_search_is_escaped(): void
    {
        $this->get('/preview/catalog?q='.urlencode('<script>alert(1)</script>'))->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;', false);
    }

    public function test_invalid_queries_are_controlled_without_redirect_loops(): void
    {
        $this->get('/preview/catalog?q[]=x')->assertStatus(422)->assertSee('Filter belum dapat diterapkan');
        $this->get('/preview/admin/analytics?from=not-a-date')->assertStatus(422)->assertSee('Reset filter');
    }

    public function test_preview_rejects_production_even_if_routes_were_cached_in_local(): void
    {
        $this->app->instance('env', 'production');
        $this->get('/preview/admin/products')->assertNotFound();
    }
}
