<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Support\UrlValidation\UrlValidator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected Admin $admin;
    protected Category $activeCategory;
    protected Category $inactiveCategory;

    protected function setUp(): void
    {
        parent::setUp();

        UrlValidator::setDnsResolver(function ($host) {
            return ['143.244.220.150'];
        });

        $this->admin = Admin::where('email', 'admin@imersa.co.id')->first();
        if (!$this->admin) {
            $this->admin = Admin::create([
                'name' => 'Admin Test',
                'email' => 'admin@imersa.co.id',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]);
        }

        $this->activeCategory = Category::firstOrCreate(
            ['slug' => 'test-elektronik-aktif'],
            [
                'name' => 'Kategori Elektronik Aktif',
                'is_active' => true,
                'created_by_admin_id' => $this->admin->id,
            ]
        );

        $this->inactiveCategory = Category::firstOrCreate(
            ['slug' => 'test-elektronik-nonaktif'],
            [
                'name' => 'Kategori Elektronik Nonaktif',
                'is_active' => false,
                'created_by_admin_id' => $this->admin->id,
            ]
        );
    }

    public function test_active_product_creation_requires_scope_confirmation_and_active_category(): void
    {
        $this->actingAs($this->admin, 'admin');

        // Missing electronics_scope_confirmed
        $response = $this->post(route('admin.products.store'), [
            'name' => 'Laptop Gaming Baru',
            'slug' => 'laptop-gaming-baru-1',
            'is_active' => true,
            'category_id' => $this->activeCategory->id,
            // electronics_scope_confirmed is omitted
        ]);

        $response->assertSessionHasErrors('electronics_scope_confirmed');

        // With inactive category
        $response2 = $this->post(route('admin.products.store'), [
            'name' => 'Laptop Gaming Baru',
            'slug' => 'laptop-gaming-baru-2',
            'is_active' => true,
            'category_id' => $this->inactiveCategory->id,
            'electronics_scope_confirmed' => 'accepted',
        ]);

        $response2->assertSessionHasErrors('category_id');
    }

    public function test_draft_product_can_be_saved_without_scope_confirmation(): void
    {
        $this->actingAs($this->admin, 'admin');

        $slug = 'draft-headphone-' . time();
        $response = $this->post(route('admin.products.store'), [
            'name' => 'Draft Headphone Test',
            'slug' => $slug,
            'is_active' => false,
            'category_id' => $this->inactiveCategory->id,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', [
            'slug' => $slug,
            'is_active' => false,
        ]);
    }

    public function test_generic_product_edit_cannot_mass_assign_approval_status(): void
    {
        $this->actingAs($this->admin, 'admin');

        $product = Product::create([
            'name' => 'Mouse Wireless Test',
            'slug' => 'mouse-wireless-test-' . time(),
            'category_id' => $this->activeCategory->id,
            'is_active' => true,
            'affiliate_url' => 'https://shopee.co.id/mouse-1',
            'affiliate_url_status' => 'pending',
            'marketplace' => 'shopee',
        ]);

        // Attempt to maliciously escalate affiliate status via generic update
        $this->put(route('admin.products.update', $product->id), [
            'name' => 'Mouse Wireless Test Updated',
            'slug' => $product->slug,
            'category_id' => $this->activeCategory->id,
            'is_active' => true,
            'electronics_scope_confirmed' => 'accepted',
            'affiliate_url_status' => 'approved', // SHOULD BE BLOCKED
            'affiliate_url_approved_by_admin_id' => 999, // SHOULD BE BLOCKED
        ]);

        $fresh = $product->fresh();
        $this->assertEquals('pending', $fresh->affiliate_url_status, 'Affiliate status must not be mass-assignable!');
        $this->assertNull($fresh->affiliate_url_approved_by_admin_id);
    }

    public function test_dedicated_affiliate_approval_endpoint_works(): void
    {
        $this->actingAs($this->admin, 'admin');

        $product = Product::create([
            'name' => 'Keyboard Mechanical Test',
            'slug' => 'keyboard-mech-' . time(),
            'category_id' => $this->activeCategory->id,
            'is_active' => true,
            'affiliate_url' => 'https://shopee.co.id/keyboard-1',
            'affiliate_url_status' => 'pending',
            'marketplace' => 'shopee',
        ]);

        $response = $this->post(route('admin.products.affiliate-approval.store', $product->id), [
            'action' => 'approve',
            'affiliate_url' => 'https://shopee.co.id/keyboard-approved',
            'affiliate_url_provenance' => 'internal_manual',
        ]);

        $response->assertRedirect(route('admin.products.edit', $product->id));
        $fresh = $product->fresh();
        $this->assertEquals('approved', $fresh->affiliate_url_status);
        $this->assertEquals($this->admin->id, $fresh->affiliate_url_approved_by_admin_id);
        $this->assertNotNull($fresh->affiliate_url_approved_at);
    }
}
