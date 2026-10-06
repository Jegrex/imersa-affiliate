<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use DatabaseTransactions;

    protected Product $activeProduct;
    protected Product $inactiveProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = Admin::where('email', 'admin@imersa.co.id')->first();
        if (!$admin) {
            $admin = Admin::create([
                'name' => 'Admin Test',
                'email' => 'admin@imersa.co.id',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]);
        }

        $category = Category::firstOrCreate(
            ['slug' => 'test-gadget-publik'],
            [
                'name' => 'Gadget Publik',
                'is_active' => true,
                'created_by_admin_id' => $admin->id,
            ]
        );

        $this->activeProduct = Product::create([
            'name' => 'Tablet Pintar Layar Lebar',
            'slug' => 'tablet-pintar-' . uniqid('', true),
            'category_id' => $category->id,
            'is_active' => true,
            'price_amount' => 5000000.00,
            'price_currency' => 'IDR',
            'affiliate_url' => 'https://shopee.co.id/tablet-1',
            'affiliate_url_status' => 'approved',
            'affiliate_url_approved_by_admin_id' => $admin->id,
            'affiliate_url_approved_at' => \Carbon\Carbon::now('UTC'),
            'marketplace' => 'shopee',
        ]);

        $this->inactiveProduct = Product::create([
            'name' => 'Produk Draf Rahasia',
            'slug' => 'produk-draf-' . uniqid('', true),
            'category_id' => $category->id,
            'is_active' => false,
            'marketplace' => 'shopee',
        ]);
    }

    public function test_home_page_renders_successfully(): void
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('IMERSA');
        $response->assertSee('AFFILIATE');
    }

    public function test_catalog_page_filters_products_by_search_query(): void
    {
        $response = $this->get(route('catalog.index', ['q' => 'Tablet Pintar']));
        $response->assertStatus(200);
        $response->assertSee($this->activeProduct->name);

        // Search for non-existent item
        $responseNoMatch = $this->get(route('catalog.index', ['q' => 'NonExistentProductXYZ123']));
        $responseNoMatch->assertStatus(200);
        $responseNoMatch->assertSee('Tidak ada produk yang cocok');
    }

    public function test_active_product_detail_returns_200(): void
    {
        $response = $this->get(route('products.show', $this->activeProduct->slug));
        $response->assertStatus(200);
        $response->assertSee($this->activeProduct->name);
        $response->assertSee('Beli Sekarang di Shopee');
    }

    public function test_inactive_product_detail_returns_404(): void
    {
        $response = $this->get(route('products.show', $this->inactiveProduct->slug));
        $response->assertStatus(404);
    }
}
