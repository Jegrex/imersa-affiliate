<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AffiliateClick;
use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AffiliateRedirectTest extends TestCase
{
    use DatabaseTransactions;

    protected Product $approvedProduct;
    protected Product $pendingProduct;

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
            ['slug' => 'audio-headphone-test'],
            [
                'name' => 'Audio & Headphone Test',
                'is_active' => true,
                'created_by_admin_id' => $admin->id,
            ]
        );

        $now = Carbon::now('UTC');

        $this->approvedProduct = Product::create([
            'name' => 'Headset Bluetooth Pro',
            'slug' => 'headset-bt-pro-' . uniqid('', true),
            'category_id' => $category->id,
            'is_active' => true,
            'affiliate_url' => 'https://shopee.co.id/universal-link/headset-bt',
            'affiliate_url_status' => 'approved',
            'affiliate_url_provenance' => 'internal_manual',
            'affiliate_url_approved_by_admin_id' => $admin->id,
            'affiliate_url_approved_at' => $now,
            'published_at' => $now,
            'marketplace' => 'shopee',
        ]);

        $this->pendingProduct = Product::create([
            'name' => 'Headset Bluetooth Pending',
            'slug' => 'headset-bt-pending-' . uniqid('', true),
            'category_id' => $category->id,
            'is_active' => true,
            'affiliate_url' => 'https://shopee.co.id/universal-link/headset-pending',
            'affiliate_url_status' => 'pending',
            'marketplace' => 'shopee',
        ]);
    }

    public function test_approved_product_records_click_and_redirects_to_shopee(): void
    {
        $initialClickCount = AffiliateClick::where('product_id', $this->approvedProduct->id)->count();

        $response = $this->post(route('affiliate.redirect', $this->approvedProduct->id));

        $response->assertRedirect($this->approvedProduct->affiliate_url);

        $this->assertEquals(
            $initialClickCount + 1,
            AffiliateClick::where('product_id', $this->approvedProduct->id)->count(),
            'Click must be recorded in database before redirect!'
        );

        $lastClick = AffiliateClick::where('product_id', $this->approvedProduct->id)->latest('id')->first();
        $this->assertEquals($this->approvedProduct->affiliate_url, $lastClick->target_url_snapshot);
    }

    public function test_pending_affiliate_product_refuses_redirect(): void
    {
        $initialClickCount = AffiliateClick::where('product_id', $this->pendingProduct->id)->count();

        $response = $this->post(route('affiliate.redirect', $this->pendingProduct->id));

        $response->assertStatus(400);

        $this->assertEquals(
            $initialClickCount,
            AffiliateClick::where('product_id', $this->pendingProduct->id)->count(),
            'No click should be recorded when redirect fails!'
        );
    }
}
