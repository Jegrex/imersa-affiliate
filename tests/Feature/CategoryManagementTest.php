<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_category_inactivation_blocked_when_active_products_exist(): void
    {
        $this->actingAs($this->admin, 'admin');

        $category = Category::create([
            'name' => 'Monitors & Displays',
            'slug' => 'monitors-displays-' . time(),
            'is_active' => true,
            'created_by_admin_id' => $this->admin->id,
        ]);

        Product::create([
            'name' => 'Monitor 4K 27 Inch',
            'slug' => 'monitor-4k-27-' . time(),
            'category_id' => $category->id,
            'is_active' => true,
            'marketplace' => 'shopee',
        ]);

        // Attempt to update category to is_active = false
        $response = $this->put(route('admin.categories.update', $category->id), [
            'name' => $category->name,
            'slug' => $category->slug,
            'is_active' => false,
        ]);

        $response->assertSessionHasErrors('is_active');
        $this->assertTrue($category->fresh()->is_active, 'Category must remain active!');
    }

    public function test_category_deletion_blocked_when_active_products_exist(): void
    {
        $this->actingAs($this->admin, 'admin');

        $category = Category::create([
            'name' => 'Tablet & Stylus',
            'slug' => 'tablet-stylus-' . time(),
            'is_active' => true,
            'created_by_admin_id' => $this->admin->id,
        ]);

        Product::create([
            'name' => 'Grafik Tablet Pro',
            'slug' => 'grafik-tablet-pro-' . time(),
            'category_id' => $category->id,
            'is_active' => true,
            'marketplace' => 'shopee',
        ]);

        $response = $this->delete(route('admin.categories.destroy', $category->id));
        $response->assertSessionHasErrors('category');
        $this->assertNull($category->fresh()->deleted_at);
    }

    public function test_self_reference_as_parent_is_rejected(): void
    {
        $this->actingAs($this->admin, 'admin');

        $category = Category::create([
            'name' => 'Kamera Digital',
            'slug' => 'kamera-digital-' . time(),
            'is_active' => true,
            'created_by_admin_id' => $this->admin->id,
        ]);

        $response = $this->put(route('admin.categories.update', $category->id), [
            'name' => $category->name,
            'slug' => $category->slug,
            'parent_id' => $category->id, // SELF REFERENCE
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('parent_id');
    }
}
